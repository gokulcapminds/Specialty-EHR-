<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AppUrl;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

class IntakeController {

    /**
     * Send intake form link to patient (Internal Staff Endpoint)
     */
    public function send(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $patientId = !empty($input['patient_id']) ? intval($input['patient_id']) : null;

        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is required.']);
            return;
        }

        $patient = Database::fetch("SELECT id, first_name_encrypted, last_name_encrypted, email FROM patients WHERE id = ?", [$patientId]);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient record not found.']);
            return;
        }

        $firstName = EncryptionService::decrypt($patient['first_name_encrypted'] ?? '');
        $lastName = EncryptionService::decrypt($patient['last_name_encrypted'] ?? '');
        $email = $patient['email'] ?? '';

        // Generate 64-character token
        $token = bin2hex(random_bytes(32));

        Database::query(
            "INSERT INTO patient_intake_forms (patient_id, token, status) VALUES (?, ?, 'Pending')",
            [$patientId, $token]
        );

        // Honours Settings > Public EHR Base URL (or auto-detects when blank) - see AppUrl.
        $intakeUrl = AppUrl::publicUrl('intake.php?token=' . $token);

        // Attempt sending email via EmailService
        $emailSent = false;
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $subject = "Welcome to Specialty EHR - Complete Your Patient Intake Forms";
            $htmlBody = "
                <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;'>
                        <h2 style='color: #0284c7; margin-top: 0;'>Specialty EHR</h2>
                        <h3 style='color: #0f172a;'>Patient Intake Forms & Consent Request</h3>
                        <p>Hello <strong>{$firstName} {$lastName}</strong>,</p>
                        <p>Welcome to Specialty EHR. Please complete your mandatory Patient Registration Intake Forms (Consent to Treat and HIPAA Privacy Notice) prior to your visit on any mobile phone, tablet, or computer.</p>
                        <div style='text-align: center; margin: 28px 0;'>
                            <a href='{$intakeUrl}' target='_blank' style='background-color: #0284c7; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 1.05rem; display: inline-block;'>Complete Intake Forms</a>
                        </div>
                        <p style='font-size: 0.85rem; color: #64748b;'>Or copy & paste link into browser:<br><a href='{$intakeUrl}' style='color: #0284c7; word-break: break-all;'>{$intakeUrl}</a></p>
                        <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;'>
                        <p style='font-size: 0.8rem; color: #94a3b8; text-align: center;'>Specialty EHR &bull; Secure Encrypted Communication</p>
                    </div>
                </div>
            ";

            $emailSent = \App\Services\EmailService::send($email, $subject, $htmlBody);
        }

        if (isset($_SESSION['user_id'])) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'] ?? 'Staff', $_SESSION['user_role'] ?? 'Staff', $patientId, 'Send Patient Intake Link', 'Patient Directory', $patientId);
        }

        echo json_encode([
            'status' => 'success',
            'message' => $emailSent ? "Intake link emailed to {$email}." : "Intake link generated successfully.",
            'intake_url' => $intakeUrl,
            'token' => $token,
            'email_sent' => $emailSent
        ]);
    }

    /**
     * Get intake form details by token (Public Endpoint)
     */
    public function getByToken(): void {
        header('Content-Type: application/json');

        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Token is required.']);
            return;
        }

        $form = Database::fetch(
            "SELECT pif.*, p.first_name_encrypted, p.last_name_encrypted, p.email, p.dob_encrypted
             FROM patient_intake_forms pif
             JOIN patients p ON pif.patient_id = p.id
             WHERE pif.token = ?",
            [$token]
        );

        if (!$form) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired intake form token.']);
            return;
        }

        $firstName = EncryptionService::decrypt($form['first_name_encrypted'] ?? '');
        $lastName = EncryptionService::decrypt($form['last_name_encrypted'] ?? '');

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $form['id'],
                'token' => $form['token'],
                'patient_name' => "{$firstName} {$lastName}",
                'patient_first' => $firstName,
                'patient_last' => $lastName,
                'form_status' => $form['status'],
                'consent_agreed' => (bool)$form['consent_agreed'],
                'consent_name' => $form['consent_name'],
                'consent_signature' => $form['consent_signature'],
                'consent_signed_date' => $form['consent_signed_date'],
                'hipaa_agreed' => (bool)$form['hipaa_agreed'],
                'hipaa_name' => $form['hipaa_name'],
                'hipaa_signature' => $form['hipaa_signature'],
                'hipaa_signed_date' => $form['hipaa_signed_date'],
                'submitted_at' => $form['submitted_at']
            ]
        ]);
    }

    /**
     * Submit completed intake form with e-signatures (Public Endpoint)
     */
    public function submit(): void {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';

        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Form token is missing.']);
            return;
        }

        $form = Database::fetch("SELECT * FROM patient_intake_forms WHERE token = ?", [$token]);
        if (!$form) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invalid intake form token.']);
            return;
        }

        if ($form['status'] === 'Submitted' || $form['status'] === 'Approved') {
            echo json_encode(['status' => 'success', 'message' => 'Intake forms have already been submitted.']);
            return;
        }

        $consentAgreed = !empty($input['consent_agreed']) ? 1 : 0;
        $consentName = trim($input['consent_name'] ?? '');
        $consentSignature = $input['consent_signature'] ?? '';
        $consentDate = !empty($input['consent_signed_date']) ? $input['consent_signed_date'] : date('Y-m-d');

        $hipaaAgreed = !empty($input['hipaa_agreed']) ? 1 : 0;
        $hipaaName = trim($input['hipaa_name'] ?? '');
        $hipaaSignature = $input['hipaa_signature'] ?? '';
        $hipaaDate = !empty($input['hipaa_signed_date']) ? $input['hipaa_signed_date'] : date('Y-m-d');

        if (!$consentAgreed || empty($consentName) || empty($consentSignature)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please agree and sign the Consent to Treat form.']);
            return;
        }

        if (!$hipaaAgreed || empty($hipaaName) || empty($hipaaSignature)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please agree and sign the HIPAA Privacy Notice form.']);
            return;
        }

        Database::query(
            "UPDATE patient_intake_forms SET 
                status = 'Submitted',
                consent_agreed = ?,
                consent_name = ?,
                consent_signature = ?,
                consent_signed_date = ?,
                hipaa_agreed = ?,
                hipaa_name = ?,
                hipaa_signature = ?,
                hipaa_signed_date = ?,
                submitted_at = NOW()
             WHERE token = ?",
            [
                $consentAgreed, $consentName, $consentSignature, $consentDate,
                $hipaaAgreed, $hipaaName, $hipaaSignature, $hipaaDate,
                $token
            ]
        );

        // Fetch patient info to send notification emails
        $patientInfo = Database::fetch(
            "SELECT p.id, p.email, p.first_name_encrypted, p.last_name_encrypted 
             FROM patients p 
             JOIN patient_intake_forms pif ON p.id = pif.patient_id 
             WHERE pif.token = ?",
            [$token]
        );

        if ($patientInfo) {
            $firstName = EncryptionService::decrypt($patientInfo['first_name_encrypted'] ?? '');
            $lastName = EncryptionService::decrypt($patientInfo['last_name_encrypted'] ?? '');
            $patientName = "{$firstName} {$lastName}";
            $patientEmail = $patientInfo['email'] ?? '';

            // Send completion email notice to clinic staff
            $clinicNoticeSubject = "Patient Intake Forms Completed - {$patientName}";
            $clinicNoticeBody = "
                <div style='font-family: Arial, sans-serif; background: #f8fafc; padding: 24px;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;'>
                        <h2 style='color: #0284c7; margin-top: 0;'>Specialty EHR Notification</h2>
                        <h3 style='color: #0f172a;'>Patient Intake Forms Completed & Signed</h3>
                        <p>Patient <strong>{$patientName}</strong> has successfully completed and electronically signed their mandatory <strong>Consent to Treat</strong> and <strong>HIPAA Notice of Privacy Practices</strong> intake forms.</p>
                        <p style='font-size: 0.88rem; color: #475569;'>Signed Name: <strong>{$consentName}</strong> &bull; Date: <strong>{$consentDate}</strong></p>
                        <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                        <p style='font-size: 0.8rem; color: #94a3b8;'>You can view the full signed forms and signatures in the EHR Patient Directory.</p>
                    </div>
                </div>
            ";
            \App\Services\EmailService::send('sssivaprasad6@gmail.com', $clinicNoticeSubject, $clinicNoticeBody, 'Specialty EHR System');

            // Send confirmation email to patient if email present
            if (!empty($patientEmail) && filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) {
                $patientConfSubject = "Specialty EHR - Intake Forms Submission Confirmation";
                $patientConfBody = "
                    <div style='font-family: Arial, sans-serif; background: #f8fafc; padding: 24px;'>
                        <div style='max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px;'>
                            <h2 style='color: #0284c7; margin-top: 0;'>Specialty EHR</h2>
                            <h3 style='color: #166534;'>✔ Intake Forms Completed</h3>
                            <p>Hello <strong>{$patientName}</strong>,</p>
                            <p>Thank you for completing your Consent to Treat and Privacy Practices acknowledgment forms. Your signed e-records have been securely saved to your medical chart.</p>
                            <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                            <p style='font-size: 0.8rem; color: #94a3b8; text-align: center;'>Specialty EHR &bull; Encrypted Records</p>
                        </div>
                    </div>
                ";
                \App\Services\EmailService::send($patientEmail, $patientConfSubject, $patientConfBody, 'Specialty EHR');
            }
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Intake forms submitted successfully! Thank you for completing your forms.'
        ]);
    }

    /**
     * Get intake form details for a specific patient
     */
    public function getByPatientId(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');
        $patientId = intval($params['patient_id'] ?? 0);

        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid patient ID.']);
            return;
        }

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, $patientId, 'View Patient Intake Form', 'Intake', null);

        $form = Database::fetch(
            "SELECT pif.*, p.first_name_encrypted, p.last_name_encrypted
             FROM patient_intake_forms pif 
             JOIN patients p ON pif.patient_id = p.id 
             WHERE pif.patient_id = ? 
             ORDER BY pif.id DESC LIMIT 1",
            [$patientId]
        );

        if (!$form) {
            echo json_encode(['status' => 'success', 'data' => null]);
            return;
        }

        $firstName = EncryptionService::decrypt($form['first_name_encrypted'] ?? '');
        $lastName = EncryptionService::decrypt($form['last_name_encrypted'] ?? '');

        echo json_encode([
            'status' => 'success',
            'data' => [
                'id' => $form['id'] ?? null,
                'patient_id' => $form['patient_id'] ?? null,
                'patient_name' => "{$firstName} {$lastName}",
                'status' => $form['status'] ?? '',
                'consent_agreed' => (bool)($form['consent_agreed'] ?? false),
                'consent_name' => $form['consent_name'] ?? '',
                'consent_signature' => $form['consent_signature'] ?? '',
                'consent_signed_date' => $form['consent_signed_date'] ?? '',
                'hipaa_agreed' => (bool)($form['hipaa_agreed'] ?? false),
                'hipaa_name' => $form['hipaa_name'] ?? '',
                'hipaa_signature' => $form['hipaa_signature'] ?? '',
                'hipaa_signed_date' => $form['hipaa_signed_date'] ?? '',
                'submitted_at' => $form['submitted_at'] ?? ''
            ]
        ]);
    }

    private function checkAccess(): void {
        $allowedRoles = Roles::CARE_COORDINATION;   // intake forms hold patient medical history: no billing access
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    /**
     * Get recent completed intake notifications for clinic staff
     */
    public function notifications(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $notifications = Database::fetchAll(
            "SELECT pif.id, pif.patient_id, pif.submitted_at, pif.consent_name, pif.status,
                    p.first_name_encrypted, p.last_name_encrypted
             FROM patient_intake_forms pif
             JOIN patients p ON pif.patient_id = p.id
             WHERE pif.status IN ('Submitted', 'Approved')
             ORDER BY pif.submitted_at DESC LIMIT 10"
        );

        $unreadCount = 0;
        $list = [];
        foreach ($notifications as $n) {
            $firstName = EncryptionService::decrypt($n['first_name_encrypted'] ?? '');
            $lastName = EncryptionService::decrypt($n['last_name_encrypted'] ?? '');
            if ($n['status'] === 'Submitted') {
                $unreadCount++;
            }
            $list[] = [
                'id' => $n['id'],
                'patient_id' => $n['patient_id'],
                'patient_name' => "{$firstName} {$lastName}",
                'submitted_at' => $n['submitted_at'],
                'status' => $n['status']
            ];
        }

        echo json_encode([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'data' => $list
        ]);
    }

    /**
     * Staff approves & marks intake forms as reviewed
     */
    public function approve(array $params = []): void {
        header('Content-Type: application/json');
        $this->checkAccess();

        $id = intval($params['id'] ?? 0);
        if (!$id) {
            $rawInput = json_decode(file_get_contents('php://input'), true);
            $id = intval($rawInput['id'] ?? ($_GET['id'] ?? 0));
        }

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid intake form ID.']);
            return;
        }

        Database::query("UPDATE patient_intake_forms SET status = 'Approved' WHERE id = ? OR patient_id = ?", [$id, $id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Intake forms successfully approved and marked as reviewed!'
        ]);
    }
}
