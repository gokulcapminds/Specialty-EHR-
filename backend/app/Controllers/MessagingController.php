<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;
use App\Services\AuditLogger;
use App\Services\EmailService;

class MessagingController {
    private function checkAccess(): void {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }
    }

    /**
     * Get list of conversations for the current user.
     * Groups by the "other" user in the conversation.
     * Also includes a mock 'general' channel.
     */
    public function conversations(): void {
        $this->checkAccess();
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];

        // Get unique users that the current user has sent messages to OR received messages from
        $sql = "
            SELECT u.id, u.first_name, u.last_name, u.role,
                   MAX(m.created_at) as last_msg_date
            FROM users u
            LEFT JOIN secure_messages m ON (m.sender_id = $userId AND EXISTS(SELECT 1 FROM message_recipients mr WHERE mr.message_id = m.id AND mr.receiver_id = u.id))
                                        OR (m.sender_id = u.id AND EXISTS(SELECT 1 FROM message_recipients mr WHERE mr.message_id = m.id AND mr.receiver_id = $userId))
            WHERE u.id != $userId
            GROUP BY u.id
            ORDER BY last_msg_date DESC, u.first_name ASC
        ";
        
        $users = Database::fetchAll($sql);
        
        $conversations = [];
        
        // Get last message in general channel
        $generalLastMsg = Database::fetch("
            SELECT m.body_encrypted FROM secure_messages m
            LEFT JOIN message_recipients mr ON m.id = mr.message_id
            WHERE mr.id IS NULL
            ORDER BY m.created_at DESC LIMIT 1
        ");
        $generalSnippet = 'Practice-wide conversation';
        if ($generalLastMsg) {
            $dec = EncryptionService::decrypt($generalLastMsg['body_encrypted']);
            $generalSnippet = strlen($dec) > 30 ? substr($dec, 0, 30) . '...' : $dec;
        }

        $conversations[] = [
            'id' => 'general',
            'name' => 'General',
            'role' => 'Global Group Chat',
            'initials' => 'G',
            'last_message' => $generalSnippet,
            'unread_count' => 0,
            'is_group' => true
        ];

        foreach ($users as $u) {
            // Find last message snippet
            $snippet = '';
            $lastMsgSql = "
                SELECT m.body_encrypted 
                FROM secure_messages m 
                JOIN message_recipients mr ON m.id = mr.message_id
                WHERE (m.sender_id = $userId AND mr.receiver_id = {$u['id']})
                   OR (m.sender_id = {$u['id']} AND mr.receiver_id = $userId)
                ORDER BY m.created_at DESC LIMIT 1
            ";
            $lastMsg = Database::fetch($lastMsgSql);
            if ($lastMsg) {
                $decrypted = EncryptionService::decrypt($lastMsg['body_encrypted']);
                $snippet = strlen($decrypted) > 30 ? substr($decrypted, 0, 30) . '...' : $decrypted;
            }

            // Fetch unread count from this user
            $unreadSql = "
                SELECT COUNT(*) as cnt 
                FROM secure_messages m 
                JOIN message_recipients mr ON m.id = mr.message_id
                WHERE m.sender_id = {$u['id']} AND mr.receiver_id = $userId AND mr.read_at IS NULL
            ";
            $unreadCount = (int)(Database::fetch($unreadSql)['cnt'] ?? 0);

            $conversations[] = [
                'id' => $u['id'],
                'name' => $u['first_name'] . ' ' . $u['last_name'],
                'role' => $u['role'],
                'initials' => strtoupper(substr($u['first_name'], 0, 1)),
                'last_message' => $snippet ?: 'Start a conversation...',
                'unread_count' => $unreadCount,
                'is_group' => false
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $conversations]);
    }

    /**
     * Get chat history between current user and another user (or general)
     */
    public function chat($params = []): void {
        $this->checkAccess();
        header('Content-Type: application/json');
        $userId = $_SESSION['user_id'];
        $targetId = $params['id'] ?? 'general';

        $messages = [];
        if ($targetId === 'general') {
            $sql = "
                SELECT m.id, m.sender_id, m.body_encrypted, m.created_at, u.first_name, u.last_name, u.role
                FROM secure_messages m
                LEFT JOIN message_recipients mr ON m.id = mr.message_id
                JOIN users u ON m.sender_id = u.id
                WHERE mr.id IS NULL
                ORDER BY m.created_at ASC
            ";
            $msgs = Database::fetchAll($sql);
            foreach ($msgs as $m) {
                $attach = Database::fetchAll("SELECT file_path, file_name FROM message_attachments WHERE message_id = ?", [$m['id']]);
                $messages[] = [
                    'id' => $m['id'],
                    'sender_id' => $m['sender_id'],
                    'sender_name' => $m['first_name'] . ' ' . $m['last_name'],
                    'sender_role' => $m['role'],
                    'body' => EncryptionService::decrypt($m['body_encrypted']),
                    'time' => date('h:i A', strtotime($m['created_at'])),
                    'date' => date('M d, Y', strtotime($m['created_at'])),
                    'is_mine' => $m['sender_id'] == $userId,
                    'attachments' => $attach
                ];
            }
        } else {
            $otherId = (int)$targetId;
            $sql = "
                SELECT m.id, m.sender_id, m.body_encrypted, m.created_at, u.first_name, u.last_name, u.role
                FROM secure_messages m
                JOIN message_recipients mr ON m.id = mr.message_id
                JOIN users u ON m.sender_id = u.id
                WHERE (m.sender_id = $userId AND mr.receiver_id = $otherId)
                   OR (m.sender_id = $otherId AND mr.receiver_id = $userId)
                ORDER BY m.created_at ASC
            ";
            $msgs = Database::fetchAll($sql);

            // Mark as read
            Database::query("UPDATE message_recipients SET read_at = NOW() WHERE receiver_id = $userId AND read_at IS NULL AND message_id IN (SELECT id FROM secure_messages WHERE sender_id = $otherId)");

            foreach ($msgs as $m) {
                $attach = Database::fetchAll("SELECT file_path, file_name FROM message_attachments WHERE message_id = ?", [$m['id']]);
                $messages[] = [
                    'id' => $m['id'],
                    'sender_id' => $m['sender_id'],
                    'sender_name' => $m['first_name'] . ' ' . $m['last_name'],
                    'sender_role' => $m['role'],
                    'body' => EncryptionService::decrypt($m['body_encrypted']),
                    'time' => date('h:i A', strtotime($m['created_at'])),
                    'date' => date('M d, Y', strtotime($m['created_at'])),
                    'is_mine' => $m['sender_id'] == $userId,
                    'attachments' => $attach
                ];
            }
        }

        echo json_encode(['status' => 'success', 'data' => $messages]);
    }

    /**
     * Send a new chat message
     */
    public function sendChat(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $targetId = $_POST['target_id'] ?? '';
        $body = $_POST['body'] ?? '';

        if (empty($targetId)) {
            error_log("UPLOAD FAILED: Missing target id. POST data: " . print_r($_POST, true));
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing target']);
            return;
        }

        if (empty($body) && empty($_FILES['attachments']['name'][0])) {
            error_log("UPLOAD FAILED: Missing body and attachment. POST data: " . print_r($_POST, true) . " FILES: " . print_r($_FILES, true));
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Message body or attachment is required']);
            return;
        }

        $userId = $_SESSION['user_id'];
        
        Database::query("INSERT INTO secure_messages (sender_id, subject, body_encrypted, priority) VALUES (?, ?, ?, ?)", [
            $userId, 'Direct Message', EncryptionService::encrypt($body), 'Normal'
        ]);
        $msgId = Database::getConnection()->lastInsertId();

        if ($targetId !== 'general') {
            Database::query("INSERT INTO message_recipients (message_id, receiver_id) VALUES (?, ?)", [$msgId, $targetId]);
        }
        
        // Attachments
        $attachments = [];
        if (isset($_FILES['attachments'])) {
            $files = $_FILES['attachments'];
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $tmpName = $files['tmp_name'][$i];
                    $fileName = basename($files['name'][$i]);
                    // Sanitize filename
                    $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
                    $uploadDir = realpath(__DIR__ . '/../../../public') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR;
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $uniqueName = time() . '_' . uniqid() . '_' . $fileName;
                    $newPath = $uploadDir . $uniqueName;
                    $dbPath = '/storage/attachments/' . $uniqueName;
                    
                    if (move_uploaded_file($tmpName, $newPath)) {
                        Database::query("INSERT INTO message_attachments (message_id, file_path, file_name) VALUES (?, ?, ?)", [$msgId, $dbPath, $fileName]);
                        $attachments[] = ['file_name' => $fileName, 'file_path' => $dbPath];
                    } else {
                        error_log("UPLOAD FAILED: move_uploaded_file from $tmpName to $newPath failed. Upload dir: $uploadDir, writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
                    }
                } else {
                    error_log("UPLOAD ERROR CODE: " . $files['error'][$i] . " for file: " . $files['name'][$i]);
                }
            }
        }

        AuditLogger::log($userId, $_SESSION['username'], $_SESSION['user_role'], null, 'Sent Chat Message', 'Messaging', $msgId);

        // Return the newly created message object so UI can append it instantly
        $responseMsg = [
            'id' => $msgId,
            'sender_id' => $userId,
            'sender_name' => $_SESSION['username'], // Ideally first/last name, but username suffices for self
            'sender_role' => $_SESSION['user_role'],
            'body' => $body,
            'time' => date('h:i A'),
            'date' => date('M d, Y'),
            'is_mine' => true,
            'attachments' => $attachments
        ];

        echo json_encode(['status' => 'success', 'data' => $responseMsg]);
    }

    /**
     * Returns total unread message count for the current user (for dashboard badge).
     */
    public function unreadCount(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $userId = (int)$_SESSION['user_id'];

        // Count unread DM messages sent TO this user that have not been read yet
        $row = Database::fetch("
            SELECT COUNT(*) as cnt
            FROM message_recipients mr
            JOIN secure_messages m ON m.id = mr.message_id
            WHERE mr.receiver_id = $userId
              AND mr.read_at IS NULL
        ");

        $count = (int)($row['cnt'] ?? 0);
        echo json_encode(['status' => 'success', 'count' => $count]);
    }

    /**
     * Returns minimal staff info (id, first_name, last_name, role) for all active users.
     * Used by the @mention dropdown — safe for ANY authenticated user.
     */
    public function staffList(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $currentUserId = (int)$_SESSION['user_id'];

        // Exclude the current logged-in user from the list (no point @mentioning yourself)
        $users = Database::fetchAll(
            "SELECT id, first_name, last_name, role FROM users
             WHERE is_active = 1 AND id != $currentUserId
             ORDER BY first_name ASC"
        );

        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    /**
     * Get list of messages for a specific patient.
     */
    public function patientMessages(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }
        if (!Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $messages = Database::fetchAll(
            "SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) AS sender_name, u.role AS sender_role
             FROM secure_messages m
             LEFT JOIN users u ON m.sender_id = u.id
             WHERE m.patient_id = ?
             ORDER BY m.created_at DESC, m.id DESC",
            [$patientId]
        );

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, $patientId, 'View Patient Messages', 'Messaging', null);

        foreach ($messages as &$msg) {
            $msg['body'] = EncryptionService::decrypt($msg['body_encrypted']);
            unset($msg['body_encrypted']);
        }

        echo json_encode(['status' => 'success', 'data' => $messages]);
    }

    /**
     * Send / record a new message for a patient.
     */
    public function sendPatientMessage(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $_POST['patient_id'] ?? null;
        $subject = $_POST['subject'] ?? '';
        $body = $_POST['body'] ?? '';
        $category = $_POST['category'] ?? 'General';
        $priority = $_POST['priority'] ?? 'Normal';

        $patientEmail = $_POST['patient_email'] ?? '';

        if (!$patientId) {
            $rawInput = json_decode(file_get_contents('php://input'), true) ?? [];
            $patientId = $rawInput['patient_id'] ?? null;
            $patientEmail = $rawInput['patient_email'] ?? $patientEmail;
            $subject = $rawInput['subject'] ?? $subject;
            $body = $rawInput['body'] ?? $body;
            $category = $rawInput['category'] ?? $category;
            $priority = $rawInput['priority'] ?? $priority;
        }

        if (!$patientId || empty($subject) || empty($body)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID, Subject, and Message Body are required.']);
            return;
        }
        if (!Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $emailSent = false;
        if (!empty($patientEmail)) {
            Database::query("UPDATE patients SET email = ? WHERE id = ?", [$patientEmail, $patientId]);

            $emailBodyHtml = "
                <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background-color: #ffffff;'>
                    <h2 style='color: #0284c7; margin-top: 0; font-size: 1.3rem;'>New Patient Message Notification</h2>
                    <p style='color: #334155; font-size: 0.95rem;'>Dear Patient,</p>
                    <p style='color: #475569; font-size: 0.92rem; line-height: 1.5;'>You have received a new secure clinical communication from your healthcare provider.</p>
                    <div style='background-color: #f8fafc; padding: 16px; border-left: 4px solid #0284c7; margin: 16px 0; border-radius: 4px;'>
                        <p style='margin: 0 0 8px 0; color: #0f172a; font-weight: 700; font-size: 0.95rem;'>Subject: " . htmlspecialchars($subject) . "</p>
                        <p style='margin: 0 0 10px 0; color: #64748b; font-size: 0.85rem;'>Category: <strong>" . htmlspecialchars($category) . "</strong> | Priority: <strong>" . htmlspecialchars($priority) . "</strong></p>
                        <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;'>
                        <div style='color: #334155; font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap;'>" . nl2br(htmlspecialchars($body)) . "</div>
                    </div>
                    <p style='font-size: 0.82rem; color: #94a3b8; margin-bottom: 0;'>Specialty EHR Workspace • HIPAA Compliant Communication</p>
                </div>
            ";

            $emailSent = EmailService::send($patientEmail, "New Secure Message: " . $subject, $emailBodyHtml, "Specialty EHR");
        }

        $encryptedBody = EncryptionService::encrypt($body);
        $senderId = $_SESSION['user_id'] ?? 1;

        $sql = "INSERT INTO secure_messages (sender_id, patient_id, category, priority, subject, body_encrypted, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'Sent', NOW())";
        
        Database::query($sql, [$senderId, $patientId, $category, $priority, $subject, $encryptedBody]);

        AuditLogger::log($senderId, $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Sent Patient Message & Email (Sent: ' . ($emailSent ? 'Yes' : 'No') . ') to ' . $patientEmail . ': ' . $subject, 'Messaging');

        if ($emailSent) {
            $resultMsg = 'Message saved and email sent to ' . $patientEmail . '.';
        } elseif (!empty($patientEmail)) {
            $resultMsg = 'Message saved, but the email could not be sent (' . (EmailService::getLastError() ?: 'mail server error') . ').';
        } else {
            $resultMsg = 'Message saved. No email address on file, so no email was sent.';
        }
        echo json_encode(['status' => 'success', 'message' => $resultMsg, 'email_sent' => $emailSent]);
    }

    /**
     * Delete a patient message.
     */
    public function deletePatientMessage(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Message ID required.']);
            return;
        }
        if (!Database::fetch("SELECT m.id FROM secure_messages m JOIN patients p ON p.id = m.patient_id WHERE m.id = ? AND p.facility_id = ?", [$id, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Message not found.']);
            return;
        }

        Database::query("DELETE FROM secure_messages WHERE id = ?", [$id]);

        echo json_encode(['status' => 'success', 'message' => 'Message deleted successfully.']);
    }
}
