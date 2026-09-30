<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EmailService;

class UserController {
    // Canonical role vocabulary = the users.role DB ENUM, minus 'Patient' (not staff-assignable here).
    private const ASSIGNABLE_ROLES = ['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff'];

    private const USER_COLUMNS = "id, username, first_name, last_name, email, role, user_type, title, middle_name, suffix, preferred_name, job_title, employment_type, employee_id, specialty, npi, license_number, license_expiry, dea_number, dea_expiry, credential_type, sub_specialty, taxonomy_code, phone, work_phone, address, facility_id, provider_locations, provider_schedule, provider_billing, provider_preferences, custom_permissions, is_active, last_login, created_at";

    private function checkAdminAccess(): void {
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'Super Admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'User Management permission required.']);
            exit();
        }
    }

    // A staff member's specialty must be one the assigned facility actually practices.
    private function isSpecialtyAllowedForFacility(int $facilityId, string $specialty): bool {
        $allowed = Database::fetch("
            SELECT 1 FROM facility_specialties fs
            JOIN specialties s ON s.id = fs.specialty_id
            WHERE fs.facility_id = ? AND (s.specialty_name = ? OR s.specialty_key = ?)
        ", [$facilityId, $specialty, $specialty]);
        return (bool)$allowed;
    }

    // Resolves whatever the wizard submitted as "role" (a cosmetic Job Role like 'Physician (MD)',
    // a saved custom_roles template name, or already a real security role) into one of
    // ASSIGNABLE_ROLES. Falls back by user_type so an unrecognized value still lands somewhere
    // sane instead of silently collapsing every unmatched role to 'Receptionist'.
    private function resolveAssignableRole(string $role, string $userType): string {
        if (in_array($role, self::ASSIGNABLE_ROLES, true)) {
            return $role;
        }

        $customRole = Database::fetch("SELECT base_role FROM custom_roles WHERE name = ?", [$role]);
        if ($customRole && !empty($customRole['base_role'])) {
            return $customRole['base_role'];
        }

        // Covers every Job Role option the Step-2 wizard actually offers (JOB_ROLES_BY_TYPE in app.js),
        // plus the legacy template names from the Step-4 role-template cards.
        $roleMapping = [
            // Staff Member job roles
            'Medical Assistant' => 'Nurse',
            'Registered Nurse (RN)' => 'Nurse',
            'Licensed Practical Nurse (LPN)' => 'Nurse',
            'Front Desk / Receptionist' => 'Receptionist',
            'Front Desk' => 'Receptionist',
            'Billing Staff' => 'Billing Staff',
            'Practice Manager' => 'Super Admin',
            'Lab Technician' => 'Nurse',
            'Phlebotomist' => 'Nurse',
            'Health Information Tech' => 'Receptionist',
            // Provider / Clinician job roles
            'Physician (MD)' => 'Doctor',
            'Physician (DO)' => 'Doctor',
            'Physician' => 'Doctor',
            'Nurse Practitioner (NP)' => 'Doctor',
            'Physician Assistant (PA)' => 'Doctor',
            'Clinical Specialist' => 'Doctor',
            'Psychologist' => 'Therapist',
            'Physical Therapist' => 'Therapist',
            'Pharmacist (PharmD)' => 'Doctor',
            // Administrator job roles
            'Practice Administrator' => 'Super Admin',
            'Office Manager' => 'Super Admin',
            'System Administrator' => 'Super Admin',
            'IT Admin' => 'Super Admin',
            'Compliance Officer' => 'Super Admin',
        ];
        if (isset($roleMapping[$role])) {
            return $roleMapping[$role];
        }

        // Unrecognized value (e.g. "Other") - fall back by the user_type card the admin picked,
        // rather than defaulting everyone to Receptionist regardless of context.
        switch ($userType) {
            case 'Administrator':
                return 'Super Admin';
            case 'Provider / Clinician':
                return 'Doctor';
            default:
                return 'Receptionist';
        }
    }

    // Normalizes the Step-2 state-license rows into a JSON array of { state, number },
    // dropping fully-empty rows. Returns null when there's nothing to store.
    private function encodeStateLicenses($rawEntries): ?string {
        if (!is_array($rawEntries)) {
            return null;
        }
        $entries = [];
        foreach ($rawEntries as $entry) {
            $state = trim((string)($entry['state'] ?? ''));
            $number = trim((string)($entry['number'] ?? ''));
            if ($state === '' && $number === '') {
                continue;
            }
            $entries[] = ['state' => $state, 'number' => $number];
        }
        return count($entries) > 0 ? json_encode($entries) : null;
    }

    public function index(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $users = Database::fetchAll("SELECT " . self::USER_COLUMNS . " FROM users ORDER BY username ASC");
        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    public function show(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $userId = $params['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID required.']);
            return;
        }

        $user = Database::fetch("SELECT " . self::USER_COLUMNS . " FROM users WHERE id = ?", [$userId]);
        if (!$user) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        echo json_encode(['status' => 'success', 'data' => $user]);
    }

    public function store(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($input['username'] ?? '');
        $first = trim($input['first_name'] ?? '');
        $last = trim($input['last_name'] ?? '');
        $role = $input['role'] ?? '';
        $userType = trim($input['user_type'] ?? 'Staff');
        $specialty = trim($input['specialty'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = (string)($input['password'] ?? '');
        $sendCredentials = !isset($input['send_credentials']) || $input['send_credentials'] !== false;

        if (empty($username) || empty($first) || empty($last) || empty($email)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username, first name, last name, and email are required.']);
            return;
        }

        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
            return;
        }

        if (empty($specialty)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Primary Specialty is required.']);
            return;
        }

        // Map whatever the wizard submitted (a cosmetic Job Role, a saved template name, etc.)
        // to a real security role - see resolveAssignableRole() for the full mapping.
        $role = $this->resolveAssignableRole($role, $userType);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            return;
        }

        $usernameTaken = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($usernameTaken) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
            return;
        }

        $emailTaken = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
        if ($emailTaken) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'A user with this email already exists.']);
            return;
        }

        // Enterprise user profile fields
        $title         = trim($input['title'] ?? '');
        $middleName    = trim($input['middle_name'] ?? '');
        $suffix        = trim($input['suffix'] ?? '');
        $preferredName = trim($input['preferred_name'] ?? '');
        $jobTitle      = trim($input['job_title'] ?? '');
        $employmentType= trim($input['employment_type'] ?? 'Full-time');
        $employeeId    = trim($input['employee_id'] ?? '');
        $workPhone     = trim($input['work_phone'] ?? '');
        $facilityId    = isset($input['facility_id']) ? (int)$input['facility_id'] : null;
        $isActive      = isset($input['is_active']) ? intval($input['is_active']) : 1;

        if ($facilityId && !$this->isSpecialtyAllowedForFacility($facilityId, $specialty)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "The specialty '{$specialty}' is not enabled for the selected facility."]);
            return;
        }

        // Clinical credential fields (required when user_type = Provider)
        $npi = trim($input['npi_number'] ?? $input['npi'] ?? '');
        if (!empty($npi) && !preg_match('/^\d{10}$/', $npi)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'NPI must be exactly 10 digits.']);
            return;
        }
        // A provider can hold licenses in more than one state, so this is stored as a JSON array
        // of { state, number } rather than a single value.
        $license_number = $this->encodeStateLicenses($input['state_licenses'] ?? null);
        $license_expiry = !empty($input['license_expiry']) ? $input['license_expiry'] : null;
        $dea_number = trim($input['dea_number'] ?? '');
        $dea_expiry = !empty($input['dea_expiry']) ? $input['dea_expiry'] : null;
        $credential_type = trim($input['credential_type'] ?? '');
        $sub_specialty = trim($input['sub_specialty'] ?? '');
        $taxonomy_code = trim($input['taxonomy_code'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');

        $provider_locations = isset($input['provider_locations']) ? (is_string($input['provider_locations']) ? $input['provider_locations'] : json_encode($input['provider_locations'])) : null;
        $provider_schedule = isset($input['provider_schedule']) ? (is_string($input['provider_schedule']) ? $input['provider_schedule'] : json_encode($input['provider_schedule'])) : null;
        $provider_billing = isset($input['provider_billing']) ? (is_string($input['provider_billing']) ? $input['provider_billing'] : json_encode($input['provider_billing'])) : null;
        $provider_preferences = isset($input['provider_preferences']) ? (is_string($input['provider_preferences']) ? $input['provider_preferences'] : json_encode($input['provider_preferences'])) : null;
        $custom_permissions = isset($input['custom_permissions']) ? (is_string($input['custom_permissions']) ? $input['custom_permissions'] : json_encode($input['custom_permissions'])) : null;

        // Admin sets the password directly. The user must change it on first login, and it
        // expires if they never log in with it, so a leaked/unread email can't be used indefinitely.
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $tempPasswordExpires = $sendCredentials ? date('Y-m-d H:i:s', strtotime('+72 hours')) : null;

        try {
            $sql = "INSERT INTO users (
                username, password_hash, first_name, last_name, email, role,
                user_type, title, middle_name, suffix, preferred_name, job_title, employment_type, employee_id,
                specialty, npi, license_number, license_expiry, dea_number, dea_expiry,
                credential_type, sub_specialty, taxonomy_code,
                phone, work_phone, address, facility_id,
                provider_locations, provider_schedule, provider_billing, provider_preferences, custom_permissions,
                must_change_password, temp_password_expires, is_active, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, NOW())";
            Database::query($sql, [
                $username, $passwordHash, $first, $last, $email, $role,
                $userType, $title, $middleName, $suffix, $preferredName, $jobTitle, $employmentType, $employeeId,
                $specialty, $npi, $license_number, $license_expiry, $dea_number, $dea_expiry,
                $credential_type, $sub_specialty, $taxonomy_code,
                $phone, $workPhone, $address, $facilityId,
                $provider_locations, $provider_schedule, $provider_billing, $provider_preferences, $custom_permissions,
                $tempPasswordExpires, $isActive
            ]);
        } catch (\Throwable $e) {
            error_log('UserController::store DB error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not create the user record. Please check the submitted details and try again.']);
            return;
        }

        $newId = Database::lastInsertId();

        $emailSent = false;
        if ($sendCredentials) {
            $emailSent = $this->sendCredentialsEmail($email, $first, $username, $password);
        }

        AuditLogger::log(
            $_SESSION['user_id'] ?? null,
            $_SESSION['username'] ?? 'Super Admin',
            $_SESSION['user_role'] ?? 'Super Admin',
            $newId,
            $sendCredentials ? 'Create User & Send Credentials: ' . $username : 'Create User (Draft, credentials not sent): ' . $username,
            'Administration',
            $newId
        );

        echo json_encode([
            'status' => 'success',
            'message' => $sendCredentials
                ? ($emailSent ? "User created and login credentials were emailed to {$email}." : 'User created, but the credentials email could not be sent. You can resend it from the user list.')
                : 'User saved as a draft. No credentials have been sent yet.',
            'id' => $newId
        ]);
    }

    public function update(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $userId = $params['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID required.']);
            return;
        }

        $existingUser = Database::fetch("SELECT id FROM users WHERE id = ?", [$userId]);
        if (!$existingUser) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($input['username'] ?? '');
        $first = trim($input['first_name'] ?? '');
        $last = trim($input['last_name'] ?? '');
        $role = $input['role'] ?? '';
        $userType = trim($input['user_type'] ?? 'Staff');
        $specialty = trim($input['specialty'] ?? '');
        $email = trim($input['email'] ?? '');
        $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;
        $newPassword = (string)($input['password'] ?? '');
        $sendCredentials = !isset($input['send_credentials']) || $input['send_credentials'] !== false;

        if (empty($username) || empty($first) || empty($last) || empty($email)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username, first name, last name, and email are required.']);
            return;
        }

        if (!empty($newPassword) && strlen($newPassword) < 8) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters.']);
            return;
        }

        if (empty($specialty)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Primary Specialty is required.']);
            return;
        }

        $role = $this->resolveAssignableRole($role, $userType);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please enter a valid email address.']);
            return;
        }

        $usernameTaken = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $userId]);
        if ($usernameTaken) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
            return;
        }

        $emailTaken = Database::fetch("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        if ($emailTaken) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'A user with this email already exists.']);
            return;
        }

        // Enterprise user profile fields
        $title         = trim($input['title'] ?? '');
        $middleName    = trim($input['middle_name'] ?? '');
        $suffix        = trim($input['suffix'] ?? '');
        $preferredName = trim($input['preferred_name'] ?? '');
        $jobTitle      = trim($input['job_title'] ?? '');
        $employmentType= trim($input['employment_type'] ?? 'Full-time');
        $employeeId    = trim($input['employee_id'] ?? '');
        $workPhone     = trim($input['work_phone'] ?? '');
        $facilityId    = isset($input['facility_id']) ? (int)$input['facility_id'] : null;

        if ($facilityId && !$this->isSpecialtyAllowedForFacility($facilityId, $specialty)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => "The specialty '{$specialty}' is not enabled for the selected facility."]);
            return;
        }

        $npi = trim($input['npi_number'] ?? $input['npi'] ?? '');
        if (!empty($npi) && !preg_match('/^\d{10}$/', $npi)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'NPI must be exactly 10 digits.']);
            return;
        }
        $license_number = $this->encodeStateLicenses($input['state_licenses'] ?? null);
        $license_expiry = !empty($input['license_expiry']) ? $input['license_expiry'] : null;
        $dea_number = trim($input['dea_number'] ?? '');
        $dea_expiry = !empty($input['dea_expiry']) ? $input['dea_expiry'] : null;
        $credential_type = trim($input['credential_type'] ?? '');
        $sub_specialty = trim($input['sub_specialty'] ?? '');
        $taxonomy_code = trim($input['taxonomy_code'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $address = trim($input['address'] ?? '');

        $provider_locations = isset($input['provider_locations']) ? (is_string($input['provider_locations']) ? $input['provider_locations'] : json_encode($input['provider_locations'])) : null;
        $provider_schedule = isset($input['provider_schedule']) ? (is_string($input['provider_schedule']) ? $input['provider_schedule'] : json_encode($input['provider_schedule'])) : null;
        $provider_billing = isset($input['provider_billing']) ? (is_string($input['provider_billing']) ? $input['provider_billing'] : json_encode($input['provider_billing'])) : null;
        $provider_preferences = isset($input['provider_preferences']) ? (is_string($input['provider_preferences']) ? $input['provider_preferences'] : json_encode($input['provider_preferences'])) : null;
        $custom_permissions = isset($input['custom_permissions']) ? (is_string($input['custom_permissions']) ? $input['custom_permissions'] : json_encode($input['custom_permissions'])) : null;

        try {
            $commonFields = [
                $username, $first, $last, $email, $role,
                $userType, $title, $middleName, $suffix, $preferredName, $jobTitle, $employmentType, $employeeId,
                $specialty, $npi, $license_number, $license_expiry, $dea_number, $dea_expiry,
                $credential_type, $sub_specialty, $taxonomy_code,
                $phone, $workPhone, $address, $facilityId,
                $provider_locations, $provider_schedule, $provider_billing, $provider_preferences, $custom_permissions,
                $isActive
            ];
            if (!empty($newPassword)) {
                // Admin is setting a new password directly - the user must change it on next login.
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $tempPasswordExpires = ($isActive && $sendCredentials) ? date('Y-m-d H:i:s', strtotime('+72 hours')) : null;
                $sql = "UPDATE users SET
                    username = ?, first_name = ?, last_name = ?, email = ?, role = ?,
                    user_type = ?, title = ?, middle_name = ?, suffix = ?, preferred_name = ?, job_title = ?, employment_type = ?, employee_id = ?,
                    specialty = ?, npi = ?, license_number = ?, license_expiry = ?, dea_number = ?, dea_expiry = ?,
                    credential_type = ?, sub_specialty = ?, taxonomy_code = ?,
                    phone = ?, work_phone = ?, address = ?, facility_id = ?,
                    provider_locations = ?, provider_schedule = ?, provider_billing = ?, provider_preferences = ?, custom_permissions = ?,
                    is_active = ?, password_hash = ?, must_change_password = 1, temp_password_expires = ?
                    WHERE id = ?";
                Database::query($sql, array_merge($commonFields, [$hash, $tempPasswordExpires, $userId]));
            } else {
                $sql = "UPDATE users SET
                    username = ?, first_name = ?, last_name = ?, email = ?, role = ?,
                    user_type = ?, title = ?, middle_name = ?, suffix = ?, preferred_name = ?, job_title = ?, employment_type = ?, employee_id = ?,
                    specialty = ?, npi = ?, license_number = ?, license_expiry = ?, dea_number = ?, dea_expiry = ?,
                    credential_type = ?, sub_specialty = ?, taxonomy_code = ?,
                    phone = ?, work_phone = ?, address = ?, facility_id = ?,
                    provider_locations = ?, provider_schedule = ?, provider_billing = ?, provider_preferences = ?, custom_permissions = ?,
                    is_active = ?
                    WHERE id = ?";
                Database::query($sql, array_merge($commonFields, [$userId]));
            }
        } catch (\Throwable $e) {
            error_log('UserController::update DB error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not update the user record. Please check the submitted details and try again.']);
            return;
        }

        $emailSent = false;
        if (!empty($newPassword) && $isActive && $sendCredentials) {
            $emailSent = $this->sendCredentialsEmail($email, $first, $username, $newPassword);
        }

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $userId,
            !empty($newPassword) ? 'Update User & Reset Password: ' . $username : 'Update User ID: ' . $userId,
            'Administration', $userId
        );

        $message = 'User profile updated successfully.';
        if (!empty($newPassword) && $isActive && $sendCredentials) {
            $message .= $emailSent ? ' New login credentials were emailed to the user.' : ' However, the credentials email could not be sent.';
        }

        echo json_encode(['status' => 'success', 'message' => $message]);
    }

    public function delete(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $userId = $params['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID required.']);
            return;
        }

        if (intval($userId) === intval($_SESSION['user_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own active session user.']);
            return;
        }

        Database::query("DELETE FROM users WHERE id = ?", [$userId]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Delete User ID: ' . $userId, 'Administration');

        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    }

    /**
     * Emails a newly (or re-)issued username/password directly to the user.
     * The account itself enforces the follow-up: must_change_password blocks
     * normal use until they set their own password, and temp_password_expires
     * rejects login if the credentials go unused past their window.
     */
    private function sendCredentialsEmail(string $email, string $firstName, string $username, string $plainPassword): bool {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/specialty_ehr/public/index.php';
        $baseDir = dirname(dirname($scriptPath));
        if ($baseDir === '/' || $baseDir === '\\') $baseDir = '';
        $loginUrl = "{$protocol}://{$host}{$baseDir}/public/index.php#login";

        $subject = 'Your Specialty EHR account is ready';
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Specialty EHR</h2>
                    <h3 style='color: #0f172a;'>Your account is ready</h3>
                    <p>Hi {$firstName},</p>
                    <p>An administrator has created an account for you. Here are your login credentials:</p>
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 10px 14px; background: #f1f5f9; border: 1px solid #e2e8f0; font-weight: bold; width: 40%;'>Username</td>
                            <td style='padding: 10px 14px; border: 1px solid #e2e8f0;'>{$username}</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px 14px; background: #f1f5f9; border: 1px solid #e2e8f0; font-weight: bold;'>Temporary Password</td>
                            <td style='padding: 10px 14px; border: 1px solid #e2e8f0;'>{$plainPassword}</td>
                        </tr>
                    </table>
                    <div style='text-align: center; margin: 28px 0;'>
                        <a href='{$loginUrl}' target='_blank' style='background-color: #4f46e5; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 1.05rem; display: inline-block;'>Log In</a>
                    </div>
                    <p style='font-size: 0.85rem; color: #64748b;'>You will be asked to choose your own password the first time you log in.</p>
                    <p style='font-size: 0.8rem; color: #94a3b8;'>For your security, this temporary password expires in 72 hours if unused. If it expires, ask your administrator to resend your credentials.</p>
                    <p style='font-size: 0.8rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 12px; margin-top: 16px;'>Don't see this in your inbox next time? Check your Spam/Junk folder and mark this sender as \"Not Spam\" so future messages arrive normally.</p>
                </div>
            </div>
        ";

        return EmailService::send($email, $subject, $htmlBody);
    }

}
