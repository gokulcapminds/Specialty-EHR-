<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class SettingsController {
    public function get(): void {
        header('Content-Type: application/json');

        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM system_settings");
        $settings = [];
        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];
            // Auto decode JSON if applicable
            if (($json = json_decode($val, true)) !== null && (is_array($json) || is_object($json))) {
                $settings[$key] = $json;
            } else {
                $settings[$key] = $val;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $settings]);
    }

    public function save(): void {
        $userRole = $_SESSION['user_role'] ?? '';
        if ($userRole !== 'Super Admin' && $userRole !== 'Doctor') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Admin or Doctor permission required to save settings.']);
            return;
        }

        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid settings payload.']);
            return;
        }

        foreach ($input as $key => $val) {
            $key = trim($key);
            $valStr = is_array($val) ? json_encode($val) : (string)$val;
            
            $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            Database::query($sql, [$key, $valStr]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Update System Settings', 'Settings');

        echo json_encode(['status' => 'success', 'message' => 'System settings saved successfully.']);
    }
}
