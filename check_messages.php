<?php
$config = require __DIR__ . '/backend/config/database.php';
require __DIR__ . '/backend/app/Models/Database.php';
require __DIR__ . '/backend/app/Services/EncryptionService.php';

use App\Models\Database;
use App\Services\EncryptionService;

class FakeDB {
    public static $pdo;
}
FakeDB::$pdo = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'].';charset=utf8', $config['username'], $config['password']);

$currentUserId = 2; // John Smith
$notifications = [];
$messageUnread = 0;

try {
    $stmt = FakeDB::$pdo->prepare("SELECT m.id, m.sender_id, m.body_encrypted, m.created_at, mr.read_at,
            su.first_name AS sender_first, su.last_name AS sender_last, su.role AS sender_role
     FROM secure_messages m
     JOIN message_recipients mr ON m.id = mr.message_id
     LEFT JOIN users su ON m.sender_id = su.id
     WHERE mr.receiver_id = ? AND mr.read_at IS NULL
     ORDER BY m.created_at DESC LIMIT 100");
    $stmt->execute([$currentUserId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo "Empty messages\n";
    } else {
        foreach ($messages as $m) {
            $senderName = trim(($m['sender_first'] ?? '') . ' ' . ($m['sender_last'] ?? '')) ?: 'Staff Member';
            $senderRole = !empty($m['sender_role']) ? " ({$m['sender_role']})" : '';
            $messageUnread++;

            $decryptedBody = !empty($m['body_encrypted']) ? EncryptionService::decrypt($m['body_encrypted']) : 'New unread secure message';
            $subtitle = strlen($decryptedBody) > 60 ? substr($decryptedBody, 0, 57) . '...' : $decryptedBody;

            $notifications[] = [
                'id' => 'msg_' . $m['id'],
                'type' => 'message',
                'category' => 'Messages',
                'title' => "Secure Message from {$senderName}{$senderRole}",
                'subtitle' => $subtitle,
                'time' => $m['created_at'] ?? 'Recently',
                'unread' => true,
                'status' => 'Unread',
                'action_type' => 'messaging_tab'
            ];
        }
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "Message Unread: $messageUnread\n";
echo "Notifications:\n";
print_r($notifications);
