<?php
require_once __DIR__ . '/backend/bootstrap/app.php';

$t0 = microtime(true);
$total = \App\Models\Database::fetch("SELECT COUNT(*) AS n FROM audit_logs")['n'];
$t1 = microtime(true);
echo "Count query: " . round($t1 - $t0, 4) . "s (Total: $total)\n";

$t0 = microtime(true);
$rows = \App\Models\Database::fetchAll("SELECT id, DATE_FORMAT(`timestamp`, '%Y-%m-%dT%H:%i:%sZ') AS `timestamp`, username, user_role, patient_id, action_type, target_module, ip_address, record_id, log_hash FROM audit_logs ORDER BY id DESC LIMIT 50");
$t1 = microtime(true);
echo "Fetch 50 rows query: " . round($t1 - $t0, 4) . "s\n";

$t0 = microtime(true);
$modules = \App\Models\Database::fetchAll("SELECT DISTINCT target_module AS v FROM audit_logs ORDER BY v");
$t1 = microtime(true);
echo "Distinct modules query: " . round($t1 - $t0, 4) . "s\n";

$t0 = microtime(true);
$roles = \App\Models\Database::fetchAll("SELECT DISTINCT user_role AS v FROM audit_logs WHERE user_role IS NOT NULL ORDER BY v");
$t1 = microtime(true);
echo "Distinct roles query: " . round($t1 - $t0, 4) . "s\n";

$t0 = microtime(true);
$v = \App\Services\AuditLogger::verifyChain();
$t1 = microtime(true);
echo "verifyChain(): " . round($t1 - $t0, 4) . "s (Checked: {$v['checked']})\n";

$t0 = microtime(true);
// Decrypt 50 patients
$ids = array_values(array_unique(array_filter(array_map(fn($r) => $r['patient_id'], $rows))));
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $pts = \App\Models\Database::fetchAll("SELECT id, first_name_encrypted, last_name_encrypted FROM patients WHERE id IN ($in)", $ids);
    foreach ($pts as $p) {
        \App\Services\EncryptionService::decrypt($p['first_name_encrypted']);
        \App\Services\EncryptionService::decrypt($p['last_name_encrypted']);
    }
}
$t1 = microtime(true);
echo "Patients decryption query: " . round($t1 - $t0, 4) . "s\n";
