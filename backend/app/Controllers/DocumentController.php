<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class DocumentController {
    private function checkAccess(array $allowedRoles): void {
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    public function upload(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        if (!isset($_FILES['document'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
            return;
        }

        $file = $_FILES['document'];
        $patientId = $_POST['patient_id'] ?? null;

        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is required.']);
            return;
        }

        // 1. Extension Validation
        $originalFilename = $file['name'];
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'jpeg', 'jpg', 'png', 'dcm', 'dicom'];

        if (!in_array($ext, $allowedExtensions)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file extension. Allowed: PDF, JPEG, PNG, DICOM.']);
            return;
        }

        // 2. MIME Validation
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'application/dicom',
            'application/octet-stream' // sometimes DICOM files return octet-stream
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid file content type (MIME).']);
            return;
        }

        // 3. Virus Scan Hook (Simulated)
        // In actual HIPAA setups, we hook with a scanner like ClamAV: `exec('clamscan --stdout ' . escapeshellarg($file['tmp_name']), $output, $returnCode);`
        $scanClean = true; // Simulated clean scan

        if (!$scanClean) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Malware detected in upload. File blocked.']);
            return;
        }

        // 4. Generate UUID name and save outside public root
        $uuid = bin2hex(random_bytes(16)) . '.' . $ext;
        $storageDir = dirname(__DIR__, 3) . '/storage/documents';

        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $destinationPath = $storageDir . '/' . $uuid;

        if (move_uploaded_file($file['tmp_name'], $destinationPath)) {
            // Save to DB
            $sql = "INSERT INTO patient_documents (patient_id, uploaded_by, original_filename, stored_filename, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)";
            Database::query($sql, [
                $patientId,
                $_SESSION['user_id'],
                $originalFilename,
                $uuid,
                $mimeType,
                $file['size']
            ]);
            $newId = Database::lastInsertId();

            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Upload Document', 'Documents', $newId);

            echo json_encode(['status' => 'success', 'message' => 'Document uploaded successfully.', 'document_id' => $newId]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to store document file.']);
        }
    }

    public function index(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Billing Staff']);
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is missing.']);
            return;
        }

        $sql = "SELECT d.id, d.original_filename, d.mime_type, d.file_size, d.uploaded_at, u.first_name, u.last_name 
                FROM patient_documents d
                JOIN users u ON d.uploaded_by = u.id
                WHERE d.patient_id = ?
                ORDER BY d.uploaded_at DESC";
        
        $docs = Database::fetchAll($sql, [$patientId]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'View Document Directory', 'Documents');

        echo json_encode(['status' => 'success', 'data' => $docs]);
    }

    public function download(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Billing Staff']);

        $docId = $params['id'] ?? null;
        if (!$docId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Document ID required.']);
            return;
        }

        $doc = Database::fetch("SELECT * FROM patient_documents WHERE id = ?", [$docId]);
        if (!$doc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Document not found.']);
            return;
        }

        $storageDir = dirname(__DIR__, 3) . '/storage/documents';
        $filePath = $storageDir . '/' . $doc['stored_filename'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Physical file missing from storage.']);
            return;
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $doc['patient_id'], 'View/Download Document (' . $doc['original_filename'] . ')', 'Documents', $docId);

        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: inline; filename="' . addslashes($doc['original_filename']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');

        readfile($filePath);
        exit();
    }

    public function delete(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        $docId = $params['id'] ?? null;
        if (!$docId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Document ID required.']);
            return;
        }

        $doc = Database::fetch("SELECT * FROM patient_documents WHERE id = ?", [$docId]);
        if (!$doc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Document not found.']);
            return;
        }

        $storageDir = dirname(__DIR__, 3) . '/storage/documents';
        $filePath = $storageDir . '/' . $doc['stored_filename'];

        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        Database::query("DELETE FROM patient_documents WHERE id = ?", [$docId]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $doc['patient_id'], 'Delete Document (' . $doc['original_filename'] . ')', 'Documents', $docId);

        echo json_encode(['status' => 'success', 'message' => 'Document deleted successfully.']);
    }
}
