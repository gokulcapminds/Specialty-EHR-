<?php
$config = require __DIR__ . '/backend/config/database.php';
require __DIR__ . '/backend/app/Models/Database.php';
use App\Models\Database;
$userId = 2; $otherId = 1;
try {
    Database::query("UPDATE message_recipients SET read_at = NOW() WHERE receiver_id = $userId AND read_at IS NULL AND message_id IN (SELECT id FROM secure_messages WHERE sender_id = $otherId)");
    echo "Success\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
