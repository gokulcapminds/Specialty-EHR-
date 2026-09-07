<?php
namespace App\Controllers;

use App\Models\Database;

class AuditController {
    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.']);
            exit();
        }
    }

    public function index(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        try {
            $logs = Database::fetchAll(
                "SELECT id, created_at, username, user_role, patient_id, action_type, target_module, ip_address, log_hash
                 FROM audit_logs
                 ORDER BY id DESC LIMIT 50"
            );

            echo json_encode([
                'status' => 'success',
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'success',
                'data' => []
            ]);
        }
    }
}
