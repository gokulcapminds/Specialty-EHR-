<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;

class ReferralController {
    private function checkAccess(): void {
        // Referrals are handled by clinical staff and the front desk; billing staff have no access.
        Roles::enforce(Roles::CARE_COORDINATION);
    }

    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $referrals = Database::fetchAll(
            "SELECT r.*, 
                    p.first_name_encrypted, p.last_name_encrypted,
                    CONCAT(u.first_name, ' ', u.last_name) AS referring_provider_name
             FROM patient_referrals r
             LEFT JOIN patients p ON r.patient_id = p.id
             LEFT JOIN users u ON r.referring_provider_id = u.id
             ORDER BY r.referral_date DESC, r.id DESC"
        );

        foreach ($referrals as &$ref) {
            if (!empty($ref['first_name_encrypted'])) {
                $ref['patient_first_name'] = \App\Services\EncryptionService::decrypt($ref['first_name_encrypted']);
            } else {
                $ref['patient_first_name'] = 'Patient';
            }
            if (!empty($ref['last_name_encrypted'])) {
                $ref['patient_last_name'] = \App\Services\EncryptionService::decrypt($ref['last_name_encrypted']);
            } else {
                $ref['patient_last_name'] = '#' . $ref['patient_id'];
            }
            unset($ref['first_name_encrypted'], $ref['last_name_encrypted']);
        }

        echo json_encode(['status' => 'success', 'data' => $referrals]);
    }

    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $referrals = Database::fetchAll(
            "SELECT r.*, 
                    CONCAT(u.first_name, ' ', u.last_name) AS referring_provider_name
             FROM patient_referrals r
             LEFT JOIN users u ON r.referring_provider_id = u.id
             WHERE r.patient_id = ?
             ORDER BY r.referral_date DESC, r.id DESC",
            [$patientId]
        );

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, $patientId, 'View Patient Referrals', 'Referrals', null);

        echo json_encode(['status' => 'success', 'data' => $referrals]);
    }

    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $_POST['patient_id'] ?? null;
        $specialty = $_POST['specialty'] ?? '';
        $specialistName = $_POST['specialist_name'] ?? '';
        $reason = $_POST['reason_for_referral'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $status = $_POST['status'] ?? 'Pending';
        $referralDate = $_POST['referral_date'] ?? date('Y-m-d');
        $priority = $_POST['priority'] ?? 'Routine';
        $department = $_POST['department'] ?? 'Outpatient';

        if (!$patientId) {
            $rawInput = json_decode(file_get_contents('php://input'), true) ?? [];
            $patientId = $rawInput['patient_id'] ?? null;
            $specialty = $rawInput['specialty'] ?? $specialty;
            $specialistName = $rawInput['specialist_name'] ?? $specialistName;
            $reason = $rawInput['reason_for_referral'] ?? $reason;
            $notes = $rawInput['notes'] ?? $notes;
            $status = $rawInput['status'] ?? $status;
            $referralDate = $rawInput['referral_date'] ?? $referralDate;
            $priority = $rawInput['priority'] ?? $priority;
            $department = $rawInput['department'] ?? $department;
        }

        if (!$patientId || empty($specialty) || empty($specialistName)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient, Specialty, and Specialist Name are required.']);
            return;
        }

        $clinicalDoc = $notes;
        $documentPath = null;

        $rootDir = realpath(__DIR__ . '/../../..');

        if (isset($_FILES['clinical_doc']) && $_FILES['clinical_doc']['error'] === UPLOAD_ERR_OK) {
            $originalFileName = basename($_FILES['clinical_doc']['name']);
            $publicDir = $rootDir . '/public/uploads/documents/';
            $storageDir = $rootDir . '/storage/documents/';
            if (!is_dir($publicDir)) @mkdir($publicDir, 0777, true);
            if (!is_dir($storageDir)) @mkdir($storageDir, 0777, true);

            $safeFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalFileName);
            $publicFile = $publicDir . $safeFileName;
            $storageFile = $storageDir . $safeFileName;

            if (move_uploaded_file($_FILES['clinical_doc']['tmp_name'], $publicFile)) {
                @copy($publicFile, $storageFile);
                $documentPath = 'uploads/documents/' . $safeFileName;
                $clinicalDoc = 'Document Attached: ' . $originalFileName . ($notes ? ' | Notes: ' . $notes : '');
            }
        }

        $referringProviderId = $_SESSION['user_id'] ?? 1;

        $sql = "INSERT INTO patient_referrals 
                (patient_id, referring_provider_id, department, specialist_name, specialty, reason_for_referral, referral_type, priority, referral_date, clinical_documentation, document_path, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Consultation', ?, ?, ?, ?, ?, NOW())";

        Database::query($sql, [
            $patientId,
            $referringProviderId,
            $department,
            $specialistName,
            $specialty,
            $reason,
            $priority,
            $referralDate,
            $clinicalDoc,
            $documentPath,
            $status
        ]);

        $newId = Database::lastInsertId();
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Create Referral ID: ' . $newId, 'Referrals');

        echo json_encode(['status' => 'success', 'message' => 'Referral created successfully.']);
    }

    public function update(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Referral ID required.']);
            return;
        }

        $patientId = $_POST['patient_id'] ?? null;
        $specialty = $_POST['specialty'] ?? '';
        $specialistName = $_POST['specialist_name'] ?? '';
        $reason = $_POST['reason_for_referral'] ?? '';
        $notes = $_POST['notes'] ?? '';
        $status = $_POST['status'] ?? 'Pending';
        $referralDate = $_POST['referral_date'] ?? date('Y-m-d');
        $priority = $_POST['priority'] ?? 'Routine';
        $department = $_POST['department'] ?? 'Outpatient';

        if (!$patientId) {
            $rawInput = json_decode(file_get_contents('php://input'), true) ?? [];
            $patientId = $rawInput['patient_id'] ?? null;
            $specialty = $rawInput['specialty'] ?? $specialty;
            $specialistName = $rawInput['specialist_name'] ?? $specialistName;
            $reason = $rawInput['reason_for_referral'] ?? $reason;
            $notes = $rawInput['notes'] ?? $notes;
            $status = $rawInput['status'] ?? $status;
            $referralDate = $rawInput['referral_date'] ?? $referralDate;
            $priority = $rawInput['priority'] ?? $priority;
            $department = $rawInput['department'] ?? $department;
        }

        if (empty($specialty) || empty($specialistName)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty and Specialist Name are required.']);
            return;
        }

        $existing = Database::fetch("SELECT clinical_documentation, document_path FROM patient_referrals WHERE id = ?", [$id]);
        $clinicalDoc = $existing ? $existing['clinical_documentation'] : $notes;
        $documentPath = $existing ? $existing['document_path'] : null;

        $rootDir = realpath(__DIR__ . '/../../..');

        if (isset($_FILES['clinical_doc']) && $_FILES['clinical_doc']['error'] === UPLOAD_ERR_OK) {
            $originalFileName = basename($_FILES['clinical_doc']['name']);
            $publicDir = $rootDir . '/public/uploads/documents/';
            $storageDir = $rootDir . '/storage/documents/';
            if (!is_dir($publicDir)) @mkdir($publicDir, 0777, true);
            if (!is_dir($storageDir)) @mkdir($storageDir, 0777, true);

            $safeFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalFileName);
            $publicFile = $publicDir . $safeFileName;
            $storageFile = $storageDir . $safeFileName;

            if (move_uploaded_file($_FILES['clinical_doc']['tmp_name'], $publicFile)) {
                @copy($publicFile, $storageFile);
                $documentPath = 'uploads/documents/' . $safeFileName;
                $clinicalDoc = 'Document Attached: ' . $originalFileName . ($notes ? ' | Notes: ' . $notes : '');
            }
        } elseif ($notes) {
            $clinicalDoc = $notes;
        }

        $sql = "UPDATE patient_referrals 
                SET specialty = ?, specialist_name = ?, reason_for_referral = ?, clinical_documentation = ?, document_path = ?, status = ?, priority = ?, updated_at = NOW() 
                WHERE id = ?";

        Database::query($sql, [$specialty, $specialistName, $reason, $clinicalDoc, $documentPath, $status, $priority, $id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Update Referral ID: ' . $id, 'Referrals');

        echo json_encode(['status' => 'success', 'message' => 'Referral updated successfully.']);
    }

    public function delete(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Referral ID required.']);
            return;
        }

        $ref = Database::fetch("SELECT patient_id FROM patient_referrals WHERE id = ?", [$id]);
        $patientId = $ref ? $ref['patient_id'] : null;

        Database::query("DELETE FROM patient_referrals WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Delete Referral ID: ' . $id, 'Referrals');

        echo json_encode(['status' => 'success', 'message' => 'Referral deleted successfully.']);
    }

    /**
     * Accept a referral and notify patient via email.
     */
    public function accept(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Referral ID required.']);
            return;
        }

        $ref = Database::fetch("SELECT r.*, p.email, p.first_name_encrypted, p.last_name_encrypted FROM patient_referrals r LEFT JOIN patients p ON r.patient_id = p.id WHERE r.id = ?", [$id]);
        if (!$ref) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Referral record not found.']);
            return;
        }

        Database::query("UPDATE patient_referrals SET status = 'Accepted', updated_at = NOW() WHERE id = ?", [$id]);

        $patientEmail = $ref['email'] ?? '';
        $emailSent = false;
        if (!empty($patientEmail)) {
            $firstName = !empty($ref['first_name_encrypted']) ? \App\Services\EncryptionService::decrypt($ref['first_name_encrypted']) : 'Patient';
            $specialty = htmlspecialchars($ref['specialty'] ?? 'Specialist');
            $docName = htmlspecialchars($ref['specialist_name'] ?? '');

            $bodyHtml = "
                <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                    <h2 style='color: #166534; margin-top: 0;'>Specialist Referral Accepted</h2>
                    <p style='color: #334155;'>Dear {$firstName},</p>
                    <p style='color: #475569;'>Your specialist referral for <strong>{$specialty}</strong> ({$docName}) has been <strong style='color:#166534;'>ACCEPTED</strong>.</p>
                    <div style='background-color: #f0fdf4; padding: 16px; border-left: 4px solid #22c55e; color: #166534; font-weight: 600; margin: 16px 0; border-radius: 4px;'>
                        <i class='fas fa-calendar-check' style='margin-right: 8px;'></i> Please call our clinic office to schedule your appointment at your earliest convenience.
                    </div>
                    <p style='font-size: 0.85rem; color: #94a3b8; margin-bottom: 0;'>Specialty EHR Workspace • HIPAA Compliant Patient Portal</p>
                </div>
            ";

            $emailSent = \App\Services\EmailService::send($patientEmail, "Referral Accepted - Schedule Your Appointment", $bodyHtml, "Specialty EHR");
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $ref['patient_id'], "Accepted Referral ID {$id} (Patient Email Sent: " . ($emailSent ? 'Yes' : 'No') . ")", 'Referrals');

        echo json_encode(['status' => 'success', 'message' => 'Referral accepted successfully and patient notified via email.', 'email_sent' => $emailSent]);
    }

    /**
     * Reject a referral with reason and notify patient via email.
     */
    public function reject(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Referral ID required.']);
            return;
        }

        $rawInput = json_decode(file_get_contents('php://input'), true) ?? [];
        $reason = trim($_POST['rejection_reason'] ?? ($rawInput['rejection_reason'] ?? ''));

        if (empty($reason)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Rejection reason is required.']);
            return;
        }

        $ref = Database::fetch("SELECT r.*, p.email, p.first_name_encrypted, p.last_name_encrypted FROM patient_referrals r LEFT JOIN patients p ON r.patient_id = p.id WHERE r.id = ?", [$id]);
        if (!$ref) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Referral record not found.']);
            return;
        }

        Database::query("UPDATE patient_referrals SET status = 'Rejected', rejection_reason = ?, updated_at = NOW() WHERE id = ?", [$reason, $id]);

        $patientEmail = $ref['email'] ?? '';
        $emailSent = false;
        if (!empty($patientEmail)) {
            $firstName = !empty($ref['first_name_encrypted']) ? \App\Services\EncryptionService::decrypt($ref['first_name_encrypted']) : 'Patient';
            $specialty = htmlspecialchars($ref['specialty'] ?? 'Specialist');
            $docName = htmlspecialchars($ref['specialist_name'] ?? '');

            $bodyHtml = "
                <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                    <h2 style='color: #dc2626; margin-top: 0;'>Specialist Referral Status Update</h2>
                    <p style='color: #334155;'>Dear {$firstName},</p>
                    <p style='color: #475569;'>Your specialist referral for <strong>{$specialty}</strong> ({$docName}) was unable to be processed at this time.</p>
                    <div style='background-color: #fef2f2; padding: 16px; border-left: 4px solid #ef4444; color: #991b1b; margin: 16px 0; border-radius: 4px;'>
                        <strong>Reason for Rejection:</strong><br>
                        " . nl2br(htmlspecialchars($reason)) . "
                    </div>
                    <p style='color: #475569; font-size: 0.9rem;'>Please contact your primary care provider if you require an alternative specialist referral.</p>
                    <p style='font-size: 0.85rem; color: #94a3b8; margin-bottom: 0;'>Specialty EHR Workspace • HIPAA Compliant Patient Portal</p>
                </div>
            ";

            $emailSent = \App\Services\EmailService::send($patientEmail, "Referral Status Update", $bodyHtml, "Specialty EHR");
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $ref['patient_id'], "Rejected Referral ID {$id} - Reason: {$reason} (Patient Email Sent: " . ($emailSent ? 'Yes' : 'No') . ")", 'Referrals');

        echo json_encode(['status' => 'success', 'message' => 'Referral rejected and patient notified via email.', 'email_sent' => $emailSent]);
    }

    public function downloadDocument(array $params): void {
        $this->checkAccess();   // was missing: the route is now behind AuthenticationMiddleware too
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(404);
            echo "Document not found.";
            return;
        }

        $ref = Database::fetch("SELECT patient_id, document_path, clinical_documentation FROM patient_referrals WHERE id = ?", [$id]);
        if (!$ref) {
            http_response_code(404);
            echo "Referral record not found.";
            return;
        }

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, $ref['patient_id'] ?? null, 'Download Referral Document', 'Referrals', (string)$id);

        $docPath = $ref['document_path'];
        $fileName = '';

        if ($docPath) {
            $fileName = basename($docPath);
        } else if (!empty($ref['clinical_documentation']) && strpos($ref['clinical_documentation'], 'Document Attached:') !== false) {
            preg_match('/Document Attached:\s*([^|]+)/', $ref['clinical_documentation'], $m);
            if (!empty($m[1])) {
                $fileName = trim($m[1]);
            }
        }

        if (empty($fileName)) {
            http_response_code(404);
            echo "No document file attached to this referral.";
            return;
        }

        $rootDir = realpath(__DIR__ . '/../../..');
        $possibleFiles = [
            $rootDir . '/public/uploads/documents/' . $fileName,
            $rootDir . '/storage/documents/' . $fileName
        ];

        $storageGlob = glob($rootDir . '/storage/documents/*' . $fileName);
        if (!empty($storageGlob)) {
            foreach ($storageGlob as $sg) {
                $possibleFiles[] = $sg;
            }
        }

        $publicGlob = glob($rootDir . '/public/uploads/documents/*' . $fileName);
        if (!empty($publicGlob)) {
            foreach ($publicGlob as $pg) {
                $possibleFiles[] = $pg;
            }
        }

        $foundFile = null;
        foreach ($possibleFiles as $pf) {
            if (file_exists($pf) && is_file($pf)) {
                $foundFile = $pf;
                break;
            }
        }

        if (!$foundFile) {
            http_response_code(404);
            echo "File not found on server.";
            return;
        }

        $ext = strtolower(pathinfo($foundFile, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';

        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . basename($foundFile) . '"');
        header('Content-Length: ' . filesize($foundFile));
        header('Cache-Control: public, max-age=86400');

        readfile($foundFile);
        exit;
    }
}
