<?php
namespace App\Services;

use App\Models\Database;

/**
 * Tamper-evident, append-only audit trail.
 *
 * - Every entry is chained: log_hash = HMAC-SHA256(key, canonical JSON of [prev_hash, UTC timestamp, user, role, patient, action, ip, ua, module, record]).
 *   The key is derived from the encryption key, so someone with only database access cannot forge a consistent chain.
 * - audit_chain_head (one row, locked FOR UPDATE while writing) serialises writers so the chain cannot fork, and records the
 *   newest entry + count (itself HMAC'd) so deleted *tail* rows are detected too.
 * - Every field is clipped to its column length BEFORE hashing (sql_mode is empty, MySQL would otherwise silently truncate the
 *   stored value and the hash would never verify again).
 * - The table has BEFORE UPDATE / BEFORE DELETE triggers that reject changes, and no foreign keys (deleting a user or patient
 *   can never rewrite a row). Timestamps are UTC DATETIME.
 * - A failed write is never silent: it goes to error_log and storage/audit_failures.log (no PHI) and is counted on the verify screen.
 *
 * Callers use only AuditLogger::log(...). Never INSERT/UPDATE/DELETE audit_logs directly.
 */
class AuditLogger {
    /** Test seam: unit tests point these at scratch tables. Production code never changes them. */
    public static string $logTable = 'audit_logs';
    public static string $headTable = 'audit_chain_head';

    public const GENESIS = '0000000000000000000000000000000000000000000000000000000000000000';

    /** Column lengths of audit_logs (hash and storage must see the same, already-clipped, value). */
    public const LIMITS = [
        'username' => 50, 'user_role' => 50, 'action_type' => 100, 'ip_address' => 45,
        'user_agent' => 255, 'target_module' => 50, 'record_id' => 50,
    ];

    // ----------------------------------------------------------------- writing

    /**
     * @param int|null    $userId     who did it
     * @param string|null $username   who did it (failed logins: the name that was typed)
     * @param string|null $role       the actor's role (NOT the action)
     * @param mixed       $patientId  the patient whose data was touched, or null. Never a user/facility/other id.
     * @param string|null $actionType short action text, e.g. 'Update Patient Record'
     * @param string|null $module     area of the app, e.g. 'Billing', 'Administration'
     * @param string|null $recordId   id of the record acted on (any table)
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
        try {
            self::write($userId, $username, $role, $patientId, $actionType, $module, $recordId);
        } catch (\Throwable $e) {
            self::reportFailure($actionType, $e);
        }
    }

    private static function write(?int $userId, ?string $username, ?string $role, $patientId, ?string $actionType, ?string $module, ?string $recordId): void {
        $pid = (is_numeric($patientId) && (int)$patientId > 0) ? (int)$patientId : null;

        $row = [
            'user_id'       => $userId,
            'username'      => self::clip('username', $username),
            'user_role'     => self::clip('user_role', $role),
            'patient_id'    => $pid,
            'action_type'   => self::clip('action_type', ($actionType === null || $actionType === '') ? 'ACTION' : $actionType),
            'ip_address'    => self::clip('ip_address', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'user_agent'    => self::clip('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'),
            'target_module' => self::clip('target_module', ($module === null || $module === '') ? 'SYSTEM' : $module),
            'record_id'     => self::clip('record_id', $recordId),
        ];

        $log = self::t(self::$logTable);
        $head = self::t(self::$headTable);

        $pdo = Database::getConnection();
        $ownTx = !$pdo->inTransaction();
        if ($ownTx) {
            $pdo->beginTransaction();
        } else {
            // Inside the caller's transaction: if only our part fails, undo only our part.
            $pdo->exec('SAVEPOINT audit_sp');
        }

        try {
            $h = Database::fetch("SELECT last_id, last_hash, entry_count FROM `$head` WHERE id = 1 FOR UPDATE");
            if (!$h) {
                throw new \RuntimeException('audit chain head row is missing');
            }

            $row['timestamp'] = Database::fetch("SELECT UTC_TIMESTAMP() AS t")['t'];
            $prev = (string)$h['last_hash'];
            $hash = self::computeHash($row, $prev);

            Database::query(
                "INSERT INTO `$log`
                    (user_id, username, user_role, patient_id, action_type, ip_address, user_agent, target_module, record_id, `timestamp`, prev_hash, log_hash)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $row['user_id'], $row['username'], $row['user_role'], $row['patient_id'], $row['action_type'],
                    $row['ip_address'], $row['user_agent'], $row['target_module'], $row['record_id'],
                    $row['timestamp'], $prev, $hash,
                ]
            );
            $id = (int)Database::lastInsertId();
            $count = (int)$h['entry_count'] + 1;

            Database::query(
                "UPDATE `$head` SET last_id = ?, last_hash = ?, entry_count = ?, head_mac = ? WHERE id = 1",
                [$id, $hash, $count, self::headMac($id, $hash, $count)]
            );

            if ($ownTx) {
                $pdo->commit();
            } else {
                $pdo->exec('RELEASE SAVEPOINT audit_sp');
            }
        } catch (\Throwable $e) {
            if ($ownTx) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
            } else {
                try { $pdo->exec('ROLLBACK TO SAVEPOINT audit_sp'); } catch (\Throwable $ignored) {}
            }
            throw $e;
        }
    }

    // ----------------------------------------------------------------- hashing (pure; shared by writer and verifier)

    /** Clip to the column length (characters) after making the text valid UTF-8, exactly like the stored value will be. */
    public static function clip(string $field, ?string $value): ?string {
        if ($value === null) {
            return null;
        }
        return mb_substr(mb_scrub($value, 'UTF-8'), 0, self::LIMITS[$field], 'UTF-8');
    }

    /** Canonical, unambiguous text of one entry (JSON array, so no field can masquerade as another). */
    public static function canonical(array $r, string $prevHash): string {
        return json_encode([
            $prevHash,
            (string)$r['timestamp'],
            ($r['user_id'] === null || $r['user_id'] === '') ? null : (int)$r['user_id'],
            $r['username'] === null ? null : (string)$r['username'],
            $r['user_role'] === null ? null : (string)$r['user_role'],
            ($r['patient_id'] === null || $r['patient_id'] === '') ? null : (int)$r['patient_id'],
            (string)$r['action_type'],
            (string)$r['ip_address'],
            (string)$r['user_agent'],
            (string)$r['target_module'],
            $r['record_id'] === null ? null : (string)$r['record_id'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    public static function computeHash(array $r, string $prevHash): string {
        return hash_hmac('sha256', self::canonical($r, $prevHash), self::key());
    }

    public static function headMac(int $lastId, string $lastHash, int $count): string {
        return hash_hmac('sha256', json_encode([$lastId, $lastHash, $count]), self::key());
    }

    private static function key(): string {
        $config = require __DIR__ . '/../../config/security.php';
        return hash_hmac('sha256', 'audit-chain-v2', $config['encryption']['key'], true);
    }

    // ----------------------------------------------------------------- verification

    public static function newVerifyState(): array {
        return ['prev' => self::GENESIS, 'checked' => 0, 'last_id' => 0, 'problem' => null];
    }

    /** Feed rows in id order. Only the FIRST problem is recorded, but counting continues. */
    public static function verifyStep(array &$state, array $row): void {
        $state['checked']++;
        if ($state['problem'] === null) {
            if ((string)$row['prev_hash'] !== $state['prev']) {
                $state['problem'] = ['id' => (int)$row['id'], 'reason' => 'Chain link broken - an earlier entry was removed, reordered or replaced.'];
            } elseif (!hash_equals(self::computeHash($row, (string)$row['prev_hash']), (string)$row['log_hash'])) {
                $state['problem'] = ['id' => (int)$row['id'], 'reason' => 'Entry content does not match its fingerprint - it was altered.'];
            }
        }
        $state['prev'] = (string)$row['log_hash'];
        $state['last_id'] = (int)$row['id'];
    }

    /** @param array|null $head the audit_chain_head row */
    public static function verifyFinish(array $state, ?array $head): array {
        $headOk = false;
        $headReason = null;
        if (!$head) {
            $headReason = 'The chain head record is missing.';
        } elseif (!hash_equals(self::headMac((int)$head['last_id'], (string)$head['last_hash'], (int)$head['entry_count']), (string)$head['head_mac'])) {
            $headReason = 'The chain head record was altered.';
        } elseif ((int)$head['last_id'] !== $state['last_id'] || (string)$head['last_hash'] !== $state['prev'] || (int)$head['entry_count'] !== $state['checked']) {
            $headReason = 'Entries are missing from the end of the log (or were added without being recorded).';
        } else {
            $headOk = true;
        }

        $problem = $state['problem'] ?? ($headOk ? null : ['id' => null, 'reason' => $headReason]);

        return [
            'status'         => $problem === null ? 'ok' : 'tampered',
            'checked'        => $state['checked'],
            'first_problem'  => $problem,
            'head_ok'        => $headOk,
            'write_failures' => self::countFailures(),
        ];
    }

    /** Pure helper for tests and small sets. */
    public static function verifyRows(array $rows, ?array $head): array {
        $state = self::newVerifyState();
        foreach ($rows as $r) {
            self::verifyStep($state, $r);
        }
        return self::verifyFinish($state, $head);
    }

    /** Verifies the whole live table in chunks. */
    public static function verifyChain(): array {
        $log = self::t(self::$logTable);
        $head = self::t(self::$headTable);
        $state = self::newVerifyState();
        $after = 0;
        while (true) {
            $rows = Database::fetchAll("SELECT * FROM `$log` WHERE id > ? ORDER BY id ASC LIMIT 2000", [$after]);
            if (!$rows) {
                break;
            }
            foreach ($rows as $r) {
                self::verifyStep($state, $r);
                $after = (int)$r['id'];
            }
        }
        $headRow = Database::fetch("SELECT last_id, last_hash, entry_count, head_mac FROM `$head` WHERE id = 1");
        return self::verifyFinish($state, $headRow ?: null);
    }

    // ----------------------------------------------------------------- failures

    private static function failureFile(): string {
        return dirname(__DIR__, 3) . '/storage/audit_failures.log';
    }

    private static function reportFailure(?string $action, \Throwable $e): void {
        // Never include request data here: this file must stay free of PHI.
        $msg = substr(preg_replace('/\s+/', ' ', $e->getMessage()), 0, 200);
        error_log('AuditLogger write failed (' . substr((string)$action, 0, 60) . '): ' . $msg);
        $dir = dirname(self::failureFile());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents(self::failureFile(), gmdate('c') . ' | ' . substr((string)$action, 0, 60) . ' | ' . $msg . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function countFailures(): int {
        $f = self::failureFile();
        if (!is_file($f)) {
            return 0;
        }
        $n = 0;
        $h = @fopen($f, 'r');
        if ($h) {
            while (fgets($h) !== false) {
                $n++;
            }
            fclose($h);
        }
        return $n;
    }

    // ----------------------------------------------------------------- misc

    /** Table names come from static properties only; still keep them to a safe charset. */
    private static function t(string $name): string {
        if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
            throw new \InvalidArgumentException('Bad table name');
        }
        return $name;
    }
}
