<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;

class AuditController {
    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.']);
            exit();
        }
    }

    public function recentActivity(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        try {
            $logs = Database::fetchAll(
                "SELECT
                    a.id,
                    a.timestamp,
                    a.username,
                    a.action_type,
                    p.first_name_encrypted,
                    p.last_name_encrypted
                 FROM audit_logs a
                 LEFT JOIN patients p ON a.patient_id = p.id
                 WHERE a.patient_id IS NOT NULL
                 ORDER BY a.id DESC
                 LIMIT 5"
            );

            // Decrypt patient names
            foreach ($logs as &$log) {
                if (!empty($log['first_name_encrypted'])) {
                    $log['first_name'] = EncryptionService::decrypt($log['first_name_encrypted']);
                } else {
                    $log['first_name'] = null;
                }

                if (!empty($log['last_name_encrypted'])) {
                    $log['last_name'] = EncryptionService::decrypt($log['last_name_encrypted']);
                } else {
                    $log['last_name'] = null;
                }

                // Remove encrypted fields from response
                unset($log['first_name_encrypted']);
                unset($log['last_name_encrypted']);
            }

            echo json_encode([
                'status' => 'success',
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch recent activity'
            ]);
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
