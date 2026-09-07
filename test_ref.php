<?php
$config = require __DIR__ . '/backend/config/database.php';
require __DIR__ . '/backend/app/Models/Database.php';
require __DIR__ . '/backend/app/Services/EncryptionService.php';

use App\Models\Database;
use App\Services\EncryptionService;

$referrals = Database::fetchAll("SELECT r.*, p.first_name_encrypted, p.last_name_encrypted,
                        CONCAT(u.first_name, ' ', u.last_name) AS referring_user_name
                 FROM patient_referrals r
                 LEFT JOIN patients p ON r.patient_id = p.id
                 LEFT JOIN users u ON r.referring_provider_id = u.id
                 WHERE r.status IN ('Pending', 'New', 'Submitted')
                 ORDER BY r.id DESC LIMIT 100");

foreach ($referrals as $r) {
    try {
        $fname = !empty($r['first_name_encrypted']) ? EncryptionService::decrypt($r['first_name_encrypted']) : 'Patient';
        $lname = !empty($r['last_name_encrypted']) ? EncryptionService::decrypt($r['last_name_encrypted']) : '#' . $r['patient_id'];
        echo "Decrypted: $fname $lname\n";
    } catch (Exception $e) {
        echo "Error on ID {$r['id']}: " . $e->getMessage() . "\n";
    }
}
