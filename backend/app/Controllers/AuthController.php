<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Security\CSRFTokenManager;

class AuthController {
    public function login(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        header('Content-Type: application/json');

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password required.']);
            return;
        }

        // Fetch User — the login field is labeled "User Name / Email", so accept either.
        $user = Database::fetch("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1", [$username, $username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            $mustChangePassword = !empty($user['must_change_password']);

            // Admin-issued temp credentials expire if never used — reject login and require the
            // admin to resend from the user list, rather than letting a stale mailed password work forever.
            if ($mustChangePassword && !empty($user['temp_password_expires']) && strtotime($user['temp_password_expires']) < time()) {
                AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Login Blocked - Temporary Credentials Expired', 'Authentication', $user['id']);
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Your temporary login credentials have expired. Please contact your administrator to resend your account details.']);
                return;
            }

            // Active specialty comes from the user's own onboarded profile, never from client input —
            // otherwise any account could render any of the 7 specialty EHRs regardless of assignment.
            $specialty = $user['specialty'] ?: 'Cardiology';

            // Setup Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['active_specialty'] = $specialty;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['fingerprint'] = md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $_SESSION['created_time'] = time();
            $_SESSION['must_change_password'] = $mustChangePassword ? 1 : 0;

            // Generate CSRF Token for security
            $csrfToken = CSRFTokenManager::generateToken();

            // Update user last login
            Database::query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

            // HIPAA Audit log login action
            AuditLogger::log($user['id'], $user['username'], $user['role'], null, "Login Successful (Specialty: {$specialty})", 'Authentication', $user['id']);

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'specialty' => $specialty,
                    'theme' => $user['theme_preference'],
                    'force_password_change' => $mustChangePassword
                ],
                'csrf_token' => $csrfToken
            ]);
        } else {
            // HIPAA Audit log failed login attempt
            AuditLogger::log(null, $username, 'Anonymous', null, 'Login Failed - Invalid Credentials', 'Authentication');

            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    }

    public function logout(): void {
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Logout', 'Authentication', $_SESSION['user_id']);
        }

        session_unset();
        session_destroy();

        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    public function me(): void {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated.']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role'],
                'specialty' => $_SESSION['active_specialty'] ?? 'Cardiology',
                'first_name' => $_SESSION['first_name'] ?? '',
                'last_name' => $_SESSION['last_name'] ?? '',
                'force_password_change' => !empty($_SESSION['must_change_password']),
                'csrf_token' => CSRFTokenManager::generateToken()
            ]
        ]);
    }

    /**
     * Lets a logged-in user (typically one on an admin-issued temporary password)
     * set their own password. Clears must_change_password so the login/API gate lifts.
     */
    public function changePassword(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $currentPassword = (string)($input['current_password'] ?? '');
        $newPassword = (string)($input['new_password'] ?? '');
        $confirmPassword = (string)($input['confirm_password'] ?? '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Current password, new password, and confirmation are all required.']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New password and confirmation do not match.']);
            return;
        }

        if (strlen($newPassword) < 8) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New password must be at least 8 characters.']);
            return;
        }

        $user = Database::fetch("SELECT id, username, password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'] ?? '', $_SESSION['user_role'] ?? '', null, 'Password Change Failed - Incorrect Current Password', 'Authentication', $_SESSION['user_id']);
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            return;
        }

        if ($newPassword === $currentPassword) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New password must be different from the current password.']);
            return;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::query("UPDATE users SET password_hash = ?, must_change_password = 0, temp_password_expires = NULL WHERE id = ?", [$hash, $user['id']]);

        unset($_SESSION['must_change_password']);

        AuditLogger::log($user['id'], $user['username'], $_SESSION['user_role'] ?? '', null, 'Password Changed', 'Authentication', $user['id']);

        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    }
}
