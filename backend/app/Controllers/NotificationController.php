<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;

class NotificationController {
    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
    }

    public function index(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $currentUserId = $_SESSION['user_id'] ?? 1;
        $currentUserRole = $_SESSION['user_role'] ?? 'Provider';

        // Fetch current user details
        $currentUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$currentUserId]);
        $currentUserName = is_array($currentUser) ? trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? '')) : '';

        $notifications = [];
        $intakeUnread = 0;
        $referralUnread = 0;
        $messageUnread = 0;

        // 1. Intake & Signed Forms (ONLY Pending Review 'Submitted' - do NOT show 'Approved'/Reviewed forms)
        try {
            $intakeForms = Database::fetchAll(
                "SELECT pif.id, pif.patient_id, pif.submitted_at, pif.status,
                        p.first_name_encrypted, p.last_name_encrypted
                 FROM patient_intake_forms pif
                 JOIN patients p ON pif.patient_id = p.id
                 WHERE pif.status = 'Submitted'
                 ORDER BY pif.submitted_at DESC LIMIT 100"
            );

            foreach ($intakeForms as $f) {
                $fname = EncryptionService::decrypt($f['first_name_encrypted'] ?? '');
                $lname = EncryptionService::decrypt($f['last_name_encrypted'] ?? '');
                $intakeUnread++;

                $notifications[] = [
                    'id' => 'intake_' . $f['id'],
                    'type' => 'intake',
                    'category' => 'Intake',
                    'title' => "Signed Form: {$fname} {$lname}",
                    'subtitle' => "Submitted Consent & HIPAA e-signed documents needing review",
                    'patient_id' => $f['patient_id'],
                    'patient_name' => "{$fname} {$lname}",
                    'time' => $f['submitted_at'],
                    'unread' => true,
                    'status' => $f['status'],
                    'action_type' => 'intake_modal'
                ];
            }
        } catch (\Exception $e) {}

        // 2. Referrals — Only PENDING referrals show in notifications and count
        try {
            $referrals = Database::fetchAll(
                "SELECT r.*, p.first_name_encrypted, p.last_name_encrypted,
                        CONCAT(u.first_name, ' ', u.last_name) AS referring_user_name
                 FROM patient_referrals r
                 LEFT JOIN patients p ON r.patient_id = p.id
                 LEFT JOIN users u ON r.referring_provider_id = u.id
                 WHERE r.status IN ('Pending', 'New', 'Submitted')
                 ORDER BY r.id DESC LIMIT 100"
            );

            foreach ($referrals as $r) {
                $fname = !empty($r['first_name_encrypted']) ? EncryptionService::decrypt($r['first_name_encrypted']) : 'Patient';
                $lname = !empty($r['last_name_encrypted']) ? EncryptionService::decrypt($r['last_name_encrypted']) : '#' . $r['patient_id'];

                $referredTo = $r['referred_to_provider'] ?? $r['referred_to_facility'] ?? 'Specialist';
                $refFrom = !empty($r['referring_user_name']) ? $r['referring_user_name'] : 'Referring Provider';
                $reasonText = !empty($r['reason']) ? $r['reason'] : ($r['specialty'] ?? 'Specialty Referral');

                $referralUnread++;

                $notifications[] = [
                    'id' => 'ref_' . $r['id'],
                    'type' => 'referral',
                    'category' => 'Referrals',
                    'title' => "Referral: " . ($r['specialty'] ?? 'Specialist'),
                    'subtitle' => "From: {$refFrom} — Patient: {$fname} {$lname} ({$reasonText})",
                    'patient_id' => $r['patient_id'],
                    'patient_name' => "{$fname} {$lname}",
                    'referred_to' => $referredTo,
                    'time' => $r['referral_date'] ?? 'Recently',
                    'unread' => true,
                    'status' => $r['status'] ?? 'Pending',
                    'action_type' => 'referrals_tab'
                ];
            }
        } catch (\Exception $e) {}

        // 3. Secure Messages (Query secure_messages & message_recipients for logged-in user)
        try {
            $messages = Database::fetchAll(
                "SELECT m.id, m.sender_id, m.body_encrypted, m.created_at, mr.read_at,
                        su.first_name AS sender_first, su.last_name AS sender_last, su.role AS sender_role
                 FROM secure_messages m
                 JOIN message_recipients mr ON m.id = mr.message_id
                 LEFT JOIN users su ON m.sender_id = su.id
                 WHERE mr.receiver_id = ? AND mr.read_at IS NULL
                 ORDER BY m.created_at DESC LIMIT 100",
                [$currentUserId]
            );

            if (empty($messages)) {
                $messagesAlt = Database::fetchAll(
                    "SELECT m.*, 
                            su.first_name AS sender_first, su.last_name AS sender_last, su.role AS sender_role,
                            p.first_name_encrypted, p.last_name_encrypted
                     FROM direct_messages m
                     LEFT JOIN users su ON m.sender_id = su.id
                     LEFT JOIN patients p ON m.patient_id = p.id
                     WHERE m.receiver_id = ? AND (m.is_read = 0 OR m.status = 'unread')
                     ORDER BY m.id DESC LIMIT 15",
                    [$currentUserId]
                );
                foreach ($messagesAlt as $m) {
                    $senderName = trim(($m['sender_first'] ?? '') . ' ' . ($m['sender_last'] ?? '')) ?: 'Staff Member';
                    $messageUnread++;
                    $bodyText = $m['message_body'] ?? $m['content'] ?? 'New unread message';
                    $notifications[] = [
                        'id' => 'msg_' . $m['id'],
                        'type' => 'message',
                        'category' => 'Messages',
                        'title' => "Message from {$senderName}",
                        'subtitle' => strlen($bodyText) > 60 ? substr($bodyText, 0, 57) . '...' : $bodyText,
                        'time' => $m['created_at'] ?? 'Recently',
                        'unread' => true,
                        'status' => 'Unread',
                        'sender_id' => $m['sender_id'],
                        'action_type' => 'messaging_tab'
                    ];
                }
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
                        'sender_id' => $m['sender_id'],
                        'action_type' => 'messaging_tab'
                    ];
                }
            }
        } catch (\Exception $e) {}

        // Sort notifications by time descending
        usort($notifications, function($a, $b) {
            return strcmp($b['time'] ?? '', $a['time'] ?? '');
        });

        $totalUnread = $intakeUnread + $referralUnread + $messageUnread;

        echo json_encode([
            'status' => 'success',
            'unread_count' => $totalUnread,
            'intake_count' => $intakeUnread,
            'referral_count' => $referralUnread,
            'message_count' => $messageUnread,
            'data' => array_slice($notifications, 0, 25)
        ]);
    }
}
