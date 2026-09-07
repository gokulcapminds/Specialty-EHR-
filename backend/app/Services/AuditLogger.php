<?php
namespace App\Services;

use App\Models\Database;

class AuditLogger {
    /**
     * Write audit log to database and enforce tamper-evident hash chain.
     */
    public static function log(
        ?int $userId,
        ?string $username,
        ?string $role,
        ?int $patientId,
        string $actionType,
        string $module,
        ?string $recordId = null
    ): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // 1. Fetch previous log's hash
        $lastLog = Database::fetch("SELECT log_hash FROM audit_logs ORDER BY id DESC LIMIT 1");
        $prevHash = $lastLog ? $lastLog['log_hash'] : str_repeat('0', 64);

        // 2. Build hash payload
        $payload = sprintf(
            "%s|%s|%s|%s|%s|%s|%s|%s|%s|%s",
            $prevHash,
            $userId ?? 'NULL',
            $username ?? 'SYSTEM',
            $role ?? 'SYSTEM',
            $patientId ?? 'NULL',
            $actionType,
            $ip,
            $userAgent,
            $module,
            $recordId ?? 'NULL'
        );
        $currentHash = hash('sha256', $payload);

        // Verify patientId exists in patients table to satisfy FK constraint
        if ($patientId !== null) {
            $patientExists = Database::fetch("SELECT id FROM patients WHERE id = ?", [$patientId]);
            if (!$patientExists) {
                $patientId = null;
            }
        }

        try {
            // 3. Write into DB
            $sql = "INSERT INTO audit_logs 
                    (user_id, username, user_role, patient_id, action_type, ip_address, user_agent, target_module, record_id, log_hash) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            Database::query($sql, [
                $userId,
                $username,
                $role,
                $patientId,
                $actionType,
                $ip,
                $userAgent,
                $module,
                $recordId,
                $currentHash
            ]);
        } catch (\Throwable $t) {}
    }
}
