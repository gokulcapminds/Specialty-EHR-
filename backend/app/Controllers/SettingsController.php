<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EmailService;

class SettingsController {
    // Secrets that must never be sent back to the browser in plaintext, regardless of role.
    private const SECRET_KEYS = ['smtp_pass'];

    private function checkSuperAdmin(): bool {
        if (($_SESSION['user_role'] ?? '') !== 'Super Admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Super Admin permission required.']);
            return false;
        }
        return true;
    }

    public function get(): void {
        header('Content-Type: application/json');
        $isSuperAdmin = ($_SESSION['user_role'] ?? '') === 'Super Admin';

        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM system_settings");
        $settings = [];
        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];

            if (in_array($key, self::SECRET_KEYS, true)) {
                // Never return the real secret. Super Admin sees whether one is set (so the UI
                // can show a masked placeholder); anyone else doesn't get the key at all.
                if (!$isSuperAdmin) {
                    continue;
                }
                $settings[$key] = $val !== '' ? '__SET__' : '';
                continue;
            }

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
        if (!$this->checkSuperAdmin()) {
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

            // A secret field left as the masked placeholder (or blank) means "keep the current
            // value" - never overwrite a real stored secret with the placeholder itself.
            if (in_array($key, self::SECRET_KEYS, true) && ($val === '__SET__' || $val === '')) {
                continue;
            }

            $valStr = is_array($val) ? json_encode($val) : (string)$val;

            $sql = "INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            Database::query($sql, [$key, $valStr]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Update System Settings', 'Settings');

        echo json_encode(['status' => 'success', 'message' => 'System settings saved successfully.']);
    }

    // Lets an admin verify the configured SMTP credentials actually deliver mail, before
    // relying on them for real workflows like emailing new-user login credentials.
    public function testEmail(): void {
        if (!$this->checkSuperAdmin()) {
            return;
        }

        header('Content-Type: application/json');

        if (!EmailService::isSmtpConfigured()) {
            echo json_encode([
                'status' => 'error',
                'message' => 'No SMTP credentials are configured yet. Fill in Host, Username, and Password below, save, then send a test email.'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $to = trim($input['to'] ?? '');
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $to = trim($_SESSION['username'] ?? '');
            // Session may hold a username rather than an email; fall back to the account's own email.
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $user = Database::fetch("SELECT email FROM users WHERE id = ?", [$_SESSION['user_id']]);
                $to = $user['email'] ?? '';
            }
        }

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'No valid recipient email address to send the test to.']);
            return;
        }

        $subject = 'Specialty EHR - Test Email';
        $body = "
            <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;'>
                    <h2 style='color: #4f46e5; margin-top: 0;'>Specialty EHR</h2>
                    <h3 style='color: #0f172a;'>SMTP Test Email</h3>
                    <p>This is a test message confirming your configured SMTP settings can deliver mail.</p>
                    <p style='font-size: 0.85rem; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 12px;'>If this landed in Spam/Junk, mark it as \"Not Spam\" - onboarding emails to new staff/providers use the same sender and are more likely to be missed if this address isn't trusted yet.</p>
                    <p style='font-size: 0.8rem; color: #94a3b8;'>Sent " . date('r') . "</p>
                </div>
            </div>
        ";

        $sent = EmailService::send($to, $subject, $body, 'Specialty EHR');

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null,
            $sent ? "SMTP Test Email Sent to {$to}" : "SMTP Test Email Failed to {$to}: " . EmailService::getLastError(),
            'Settings'
        );

        if ($sent) {
            echo json_encode(['status' => 'success', 'message' => "Test email sent successfully to {$to}."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Could not send test email: ' . (EmailService::getLastError() ?: 'Unknown error.')]);
        }
    }
}
