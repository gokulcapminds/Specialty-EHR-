<?php
namespace App\Services;

use App\Models\Database;

class AuditLogger {
    /**
     * Write audit log to database and enforce tamper-evident hash chain.
     * Supports both full signature: ($userId, $username, $role, $patientId, $actionType, $module, $recordId)
     * and shorthand signature: ($userId, $actionType, $messageOrModule, $recordId)
     */
    public static function log(
        ?int $userId = null,
        ?string $username = null,
        ?string $role = null,
        $patientId = null,
        ?string $actionType = null,
        ?string $module = null,
        ?string $recordId = null
    ): void {
        // Detect shorthand call: e.g. log($userId, 'ACTION_TYPE', 'Details/Module', $recordId)
        if ($actionType === null && $module === null && is_string($username)) {
            $actualAction = $username;
            $actualModule = is_string($role) ? $role : 'ADMIN';
            $actualRecordId = is_string($patientId) || is_numeric($patientId) ? (string)$patientId : null;
            $actualUsername = $_SESSION['username'] ?? 'SYSTEM';
            $actualRole = $_SESSION['user_role'] ?? 'SYSTEM';
            $actualPatientId = null;

            $username = $actualUsername;
            $role = $actualRole;
            $patientId = $actualPatientId;
            $actionType = $actualAction;
            $module = $actualModule;
            $recordId = $actualRecordId;
        } else {
            $actionType = $actionType ?? 'ACTION';
            $module = $module ?? 'SYSTEM';
            if (!is_numeric($patientId)) {
                $patientId = null;
            } else {
                $patientId = (int)$patientId;
            }
        }
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
