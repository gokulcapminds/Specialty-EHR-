<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

/**
 * Audit & Reports: read-only views over the tamper-evident audit trail. Super Admin only - the log lists every
 * user's activity and (decrypted) patient names, so it is not available to other roles, whatever the Roles screen says.
 * Rows are never changed here (the table is append-only); everything this controller does is itself audited.
 */
class AuditController {
    private const PER_PAGE = [5, 10, 25, 50, 100];
    private const EXPORT_CAP = 10000;

    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            $this->fail(401, 'Unauthenticated session.');
        }
        if (($_SESSION['user_role'] ?? '') !== 'Super Admin') {
            $this->fail(403, 'Only a Super Admin can view the audit log.');
        }
    }

    private function fail(int $code, string $message): never {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }

    /** Escape LIKE wildcards so a typed % or _ is searched literally. */
    private function like(string $s): string {
        return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $s) . '%';
    }

    /** Builds the WHERE clause from the query string. Dates arrive as UTC 'YYYY-MM-DD HH:MM:SS' (the browser converts its local day). */
    private function filters(): array {
        $where = [];
        $params = [];

        foreach (['from' => '>=', 'to' => '<='] as $key => $op) {
            $v = trim((string)($_GET[$key] ?? ''));
            if ($v !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $v)) {
                $where[] = "`timestamp` $op ?";
                $params[] = $v;
            }
        }
        foreach (['username' => 'username', 'role' => 'user_role', 'module' => 'target_module'] as $q => $col) {
            $v = trim((string)($_GET[$q] ?? ''));
            if ($v !== '') {
                $where[] = "$col = ?";
                $params[] = $v;
            }
        }
        $action = trim((string)($_GET['action'] ?? ''));
        if ($action !== '') {
            $where[] = "action_type LIKE ?";
            $params[] = $this->like($action);
        }
        $pid = trim((string)($_GET['patient_id'] ?? ''));
        if ($pid !== '' && ctype_digit($pid)) {
            $where[] = "patient_id = ?";
            $params[] = (int)$pid;
        }

        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function audit(string $action): void {
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, $action, 'Audit');
    }

    // GET /api/reports/audit?page=&per_page=&from=&to=&username=&role=&module=&action=&patient_id=&open=1
    public function index(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $log = AuditLogger::$logTable;
        [$where, $params] = $this->filters();

        $perPage = (int)($_GET['per_page'] ?? 50);
        if (!in_array($perPage, self::PER_PAGE, true)) {
            $perPage = 50;
        }
        $page = max(1, (int)($_GET['page'] ?? 1));

        try {
            $total = (int)Database::fetch("SELECT COUNT(*) AS n FROM `$log` $where", $params)['n'];
            $pages = max(1, (int)ceil($total / $perPage));
            $page = min($page, $pages);
            $offset = ($page - 1) * $perPage;

            $rows = Database::fetchAll(
                "SELECT id, DATE_FORMAT(`timestamp`, '%Y-%m-%dT%H:%i:%sZ') AS `timestamp`, username, user_role, patient_id,
                        action_type, target_module, ip_address, record_id, log_hash
                 FROM `$log` $where
                 ORDER BY id DESC
                 LIMIT $perPage OFFSET $offset",
                $params
            );

            // Patient names for this page only (decrypted here, never stored in the audit table).
            $ids = array_values(array_unique(array_filter(array_map(fn($r) => $r['patient_id'], $rows))));
            $names = [];
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                foreach (Database::fetchAll("SELECT id, first_name_encrypted, last_name_encrypted FROM patients WHERE id IN ($in)", $ids) as $p) {
                    $fn = !empty($p['first_name_encrypted']) ? EncryptionService::decrypt($p['first_name_encrypted']) : '';
                    $ln = !empty($p['last_name_encrypted']) ? EncryptionService::decrypt($p['last_name_encrypted']) : '';
                    $names[(int)$p['id']] = trim("$fn $ln");
                }
            }
            foreach ($rows as &$r) {
                $r['id'] = (int)$r['id'];
                $r['patient_id'] = $r['patient_id'] === null ? null : (int)$r['patient_id'];
                // null name + an id = the patient record no longer exists (the audit entry is kept).
                $r['patient_name'] = $r['patient_id'] !== null ? ($names[$r['patient_id']] ?? null) : null;
            }
            unset($r);

            $facets = [
                'modules' => Database::fetchAll("SELECT DISTINCT target_module AS v FROM `$log` ORDER BY v"),
                'roles'   => Database::fetchAll("SELECT DISTINCT user_role AS v FROM `$log` WHERE user_role IS NOT NULL ORDER BY v"),
            ];
            $facets = array_map(fn($list) => array_column($list, 'v'), $facets);

            if (($_GET['open'] ?? '') === '1') {
                $this->audit('View Audit Log');
            }

            echo json_encode([
                'status'   => 'success',
                'data'     => $rows,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'facets'   => $facets,
            ]);
        } catch (\Throwable $e) {
            // Never pretend the log is empty when the query failed (that hid the real bug before).
            error_log('AuditController::index failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not load the audit log. Check the server error log.']);
        }
    }

    // GET /api/reports/audit/verify
    public function verify(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        try {
            $result = AuditLogger::verifyChain();
            $this->audit('Verify Audit Log Integrity: ' . ($result['status'] === 'ok' ? 'intact' : 'PROBLEM FOUND'));
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (\Throwable $e) {
            error_log('AuditController::verify failed: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not verify the audit log.']);
        }
    }

    // GET /api/reports/audit/export  (same filters; patient ids only, no names, so the file carries no extra PHI)
    public function export(): void {
        $this->checkAccess();

        $log = AuditLogger::$logTable;
        [$where, $params] = $this->filters();
        $cap = self::EXPORT_CAP;

        try {
            $rows = Database::fetchAll(
                "SELECT id, `timestamp`, username, user_role, patient_id, action_type, target_module, ip_address, record_id, prev_hash, log_hash
                 FROM `$log` $where ORDER BY id ASC LIMIT $cap",
                $params
            );
        } catch (\Throwable $e) {
            error_log('AuditController::export failed: ' . $e->getMessage());
            $this->fail(500, 'Could not export the audit log.');
        }

        $this->audit('Export Audit Log (' . count($rows) . ' entries)');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="audit-log-' . gmdate('Ymd-His') . '.csv"');
        header('X-Content-Type-Options: nosniff');

        $out = fopen('php://output', 'w');
        // Explicit separator/enclosure/escape: PHP 8.4+ deprecates relying on the default $escape (the warning text would land inside the CSV).
        fputcsv($out, ['id', 'timestamp_utc', 'username', 'role', 'patient_id', 'action', 'module', 'ip_address', 'record_id', 'prev_hash', 'log_hash'], ',', '"', '');
        foreach ($rows as $r) {
            // Usernames come from failed logins (anyone can type anything): neutralise spreadsheet formulas.
            foreach (['username', 'user_role', 'action_type', 'target_module', 'record_id'] as $col) {
                if ($r[$col] !== null && preg_match('/^[=+\-@\t\r]/', (string)$r[$col])) {
                    $r[$col] = "'" . $r[$col];
                }
            }
            fputcsv($out, array_values($r), ',', '"', '');
        }
        fclose($out);
    }
}
