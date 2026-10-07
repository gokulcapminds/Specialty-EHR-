<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AppUrl;
use App\Services\EncryptionService;
use App\Services\AuditLogger;
use App\Services\EmailService;

class TelehealthController {

    private function checkAccess(array $allowedRoles): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
        $userRole = $_SESSION['user_role'] ?? '';
        $normalizedUserRole = strtolower(trim($userRole));
        $normalizedAllowed = array_map(function($r) { return strtolower(trim($r)); }, $allowedRoles);
        if (!in_array($normalizedUserRole, $normalizedAllowed) && $normalizedUserRole !== 'super admin' && $normalizedUserRole !== 'admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    /** Patients may join this many minutes before the scheduled start (enforced server-side by joinCheck). */
    public const JOIN_EARLY_MINUTES = 5;

    private function ensureTableExists(): void {
        self::ensureSchema();
    }

    private static function ensureSchema(): void {
        try {
            $sql = "CREATE TABLE IF NOT EXISTS telehealth_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                patient_id INT NOT NULL,
                appointment_id INT DEFAULT NULL,
                created_by INT NOT NULL,
                room_name VARCHAR(120) NOT NULL UNIQUE,
                patient_email VARCHAR(150) NOT NULL,
                join_url VARCHAR(255) NOT NULL,
                jitsi_url VARCHAR(255) NOT NULL,
                status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
                scheduled_start DATETIME DEFAULT NULL,
                scheduled_end DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
                FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB;";
            Database::query($sql);
        } catch (\Throwable $t) {
            error_log("TelehealthController ensureSchema error: " . $t->getMessage());
        }
    }

    /**
     * Public helper to create telehealth link & dispatch email step-by-step for calendar appointments
     */
    public static function createAndSendForAppointment(int $appointmentId, int $patientId, int $providerId, string $startTime, ?string $endTime = null): array {
        $logPrefix = "[Calendar Telehealth Email - Appt #{$appointmentId}]";
        error_log("{$logPrefix} [Step 1/6]: Initiating telehealth link process for Patient #{$patientId} scheduled at {$startTime}.");

        self::ensureSchema();

        // Step 2: Fetch Patient Record
        $patient = Database::fetch("SELECT * FROM patients WHERE id = ?", [$patientId]);
        if (!$patient) {
            $err = "{$logPrefix} [Step 2/6 ERROR]: Patient record #{$patientId} not found in database.";
            error_log($err);
            return ['status' => 'error', 'message' => 'Patient record not found.'];
        }

        $patientName = EncryptionService::decrypt($patient['first_name_encrypted']) . ' ' . EncryptionService::decrypt($patient['last_name_encrypted']);
        $patientEmail = trim($patient['email'] ?? '');

        error_log("{$logPrefix} [Step 2/6 SUCCESS]: Patient Name: '{$patientName}', Email: '{$patientEmail}'.");

        // Step 3: Validate Patient Email
        if (empty($patientEmail) || !filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) {
            $err = "{$logPrefix} [Step 3/6 ERROR]: Patient '{$patientName}' (#{$patientId}) does not have a valid email address saved in chart profile. Email dispatch skipped.";
            error_log($err);
            return ['status' => 'error', 'message' => "Patient '{$patientName}' does not have a valid email address."];
        }

        error_log("{$logPrefix} [Step 3/6 SUCCESS]: Valid email recipient confirmed: {$patientEmail}.");

        // Step 4/5: reuse the appointment's open session (edits must not spawn new rooms/emails), else create one
        $instance = new self();
        $endTime = $endTime ?: date('Y-m-d H:i:s', strtotime($startTime) + 1800);
        $existing = Database::fetch("SELECT * FROM telehealth_sessions WHERE appointment_id = ? AND status = 'Active' ORDER BY id DESC LIMIT 1", [$appointmentId]);

        if ($existing) {
            $timeChanged = strtotime($existing['scheduled_start'] ?? '') !== strtotime($startTime)
                || strtotime($existing['scheduled_end'] ?? '') !== strtotime($endTime);
            if (!$timeChanged) {
                error_log("{$logPrefix} [Step 4/6]: Active session #{$existing['id']} already matches this time - no new email.");
                return ['status' => 'success', 'message' => 'Telehealth link already sent for this appointment.'];
            }
            Database::query("UPDATE telehealth_sessions SET scheduled_start = ?, scheduled_end = ? WHERE id = ?", [$startTime, $endTime, $existing['id']]);
            $roomName = $existing['room_name'];
            $joinUrl = $existing['join_url'];
            $rescheduled = true;
            error_log("{$logPrefix} [Step 5/6 SUCCESS]: Session #{$existing['id']} rescheduled to {$startTime}.");
        } else {
            $randomHash = strtoupper(bin2hex(random_bytes(4)));
            $roomName = 'CareHealth_Telehealth_P' . $patientId . '_' . $randomHash;
            $joinUrl = $instance->buildJoinUrl($roomName);
            $jitsiUrl = 'https://meet.jit.si/' . $roomName;
            $rescheduled = false;

            $createdBy = $_SESSION['user_id'] ?? $providerId;
            $sql = "INSERT INTO telehealth_sessions (patient_id, appointment_id, created_by, room_name, patient_email, join_url, jitsi_url, status, scheduled_start, scheduled_end) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', ?, ?)";
            Database::query($sql, [$patientId, $appointmentId, $createdBy, $roomName, $patientEmail, $joinUrl, $jitsiUrl, $startTime, $endTime]);
            $sessionId = Database::lastInsertId();
            error_log("{$logPrefix} [Step 5/6 SUCCESS]: Telehealth session #{$sessionId} stored in DB (room '{$roomName}').");
        }

        // Step 6: Dispatch Email
        error_log("{$logPrefix} [Step 6/6]: Sending HTML telehealth invitation email to {$patientEmail}...");
        $emailSent = $instance->sendTelehealthEmail($patientName, $patientEmail, $joinUrl, $roomName, $startTime, $rescheduled);

        if ($emailSent) {
            error_log("{$logPrefix} [Step 6/6 SUCCESS]: Telehealth email delivered to {$patientEmail}.");
            return ['status' => 'success', 'message' => "Telehealth invitation email sent to {$patientEmail}."];
        } else {
            $err = "{$logPrefix} [Step 6/6 ERROR]: SMTP transport failed while sending email to {$patientEmail}. Check SMTP settings.";
            error_log($err);
            return ['status' => 'error', 'message' => "Session created, but email delivery failed for {$patientEmail}."];
        }
    }

    private function buildJoinUrl(string $roomName): string {
        // Honours Settings > Public EHR Base URL (or auto-detects when blank) - see AppUrl.
        return AppUrl::publicUrl('telehealth_join.php?room=' . urlencode($roomName));
    }

    public function index(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        $this->ensureTableExists();
        header('Content-Type: application/json');

        $sql = "SELECT ts.*, 
                       p.first_name_encrypted, p.last_name_encrypted, p.email as patient_default_email,
                       u.first_name as doc_first, u.last_name as doc_last
                FROM telehealth_sessions ts
                JOIN patients p ON ts.patient_id = p.id
                JOIN users u ON ts.created_by = u.id
                WHERE p.facility_id = ?
                ORDER BY ts.id DESC";

        $rows = Database::fetchAll($sql, [$_SESSION['facility_id'] ?? null]);
        $result = [];

        foreach ($rows as $r) {
            $patientName = EncryptionService::decrypt($r['first_name_encrypted']) . ' ' . EncryptionService::decrypt($r['last_name_encrypted']);
            $result[] = [
                'id' => intval($r['id']),
                'patient_id' => intval($r['patient_id']),
                'appointment_id' => $r['appointment_id'] ? intval($r['appointment_id']) : null,
                'patient_name' => $patientName,
                'patient_email' => $r['patient_email'] ?: $r['patient_default_email'],
                'provider_name' => 'Dr. ' . $r['doc_first'] . ' ' . $r['doc_last'],
                'room_name' => $r['room_name'],
                'join_url' => $r['join_url'],
                'jitsi_url' => $r['jitsi_url'],
                'status' => $r['status'],
                'scheduled_start' => $r['scheduled_start'],
                'scheduled_end' => $r['scheduled_end'],
                'created_at' => $r['created_at']
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    public function store(): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        $this->ensureTableExists();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $patientId = intval($input['patient_id'] ?? 0);
        $appointmentId = !empty($input['appointment_id']) ? intval($input['appointment_id']) : null;
        $patientEmail = trim($input['patient_email'] ?? '');

        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Please select a valid patient.']);
            return;
        }

        // Fetch patient details
        $patient = Database::fetch("SELECT * FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $_SESSION['facility_id'] ?? null]);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient record not found.']);
            return;
        }

        $patientName = EncryptionService::decrypt($patient['first_name_encrypted']) . ' ' . EncryptionService::decrypt($patient['last_name_encrypted']);

        if (empty($patientEmail)) {
            $patientEmail = trim($patient['email'] ?? '');
        }

        if (empty($patientEmail) || !filter_var($patientEmail, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'A valid patient email address is required to send the telehealth link.']);
            return;
        }

        // Generate dynamic secure unique room name
        $randomHash = strtoupper(bin2hex(random_bytes(4)));
        $roomName = 'CareHealth_Telehealth_P' . $patientId . '_' . $randomHash;

        // Build URLs
        $joinUrl = $this->buildJoinUrl($roomName);
        $jitsiUrl = 'https://meet.jit.si/' . $roomName;

        // Insert session record
        $createdBy = $_SESSION['user_id'];
        $sql = "INSERT INTO telehealth_sessions (patient_id, appointment_id, created_by, room_name, patient_email, join_url, jitsi_url, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')";
        Database::query($sql, [$patientId, $appointmentId, $createdBy, $roomName, $patientEmail, $joinUrl, $jitsiUrl]);
        $sessionId = Database::lastInsertId();

        // Send anti-spam HTML email to patient
        $emailSent = $this->sendTelehealthEmail($patientName, $patientEmail, $joinUrl, $roomName);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Create Telehealth Session', 'Telehealth', $sessionId);

        echo json_encode([
            'status' => 'success',
            'message' => $emailSent ? 'Telehealth consultation link created and email sent successfully!' : 'Telehealth session created, but email delivery encountered an issue.',
            'data' => [
                'id' => intval($sessionId),
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'patient_email' => $patientEmail,
                'room_name' => $roomName,
                'join_url' => $joinUrl,
                'jitsi_url' => $jitsiUrl,
                'email_sent' => $emailSent
            ]
        ]);
    }

    public function resendLink(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        $this->ensureTableExists();
        header('Content-Type: application/json');

        $id = intval($params['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Telehealth session ID is required.']);
            return;
        }

        $session = Database::fetch("SELECT ts.*, p.first_name_encrypted, p.last_name_encrypted FROM telehealth_sessions ts JOIN patients p ON ts.patient_id = p.id WHERE ts.id = ? AND p.facility_id = ?", [$id, $_SESSION['facility_id'] ?? null]);
        if (!$session) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Telehealth session record not found.']);
            return;
        }

        $patientName = EncryptionService::decrypt($session['first_name_encrypted']) . ' ' . EncryptionService::decrypt($session['last_name_encrypted']);
        $patientEmail = $session['patient_email'];

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!empty($input['patient_email']) && filter_var($input['patient_email'], FILTER_VALIDATE_EMAIL)) {
            $patientEmail = trim($input['patient_email']);
            Database::query("UPDATE telehealth_sessions SET patient_email = ? WHERE id = ?", [$patientEmail, $id]);
        }

        $emailSent = $this->sendTelehealthEmail($patientName, $patientEmail, $session['join_url'], $session['room_name']);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $session['patient_id'], 'Resend Telehealth Link', 'Telehealth', $id);

        if ($emailSent) {
            echo json_encode(['status' => 'success', 'message' => "Telehealth link re-sent to {$patientEmail}."]);
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email. Please check server SMTP configuration.']);
        }
    }

    public function updateStatus(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        $this->ensureTableExists();
        header('Content-Type: application/json');

        $id = intval($params['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Session ID is required.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? 'Completed';

        if (!in_array($status, ['Active', 'Completed', 'Cancelled'])) {
            $status = 'Completed';
        }
        if (!Database::fetch("SELECT ts.id FROM telehealth_sessions ts JOIN patients p ON p.id = ts.patient_id WHERE ts.id = ? AND p.facility_id = ?", [$id, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Telehealth session record not found.']);
            return;
        }

        Database::query("UPDATE telehealth_sessions SET status = ? WHERE id = ?", [$status, $id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Update Telehealth Session Status to ' . $status, 'Telehealth', $id);

        echo json_encode(['status' => 'success', 'message' => 'Telehealth session marked as ' . $status . '.']);
    }

    /**
     * PUBLIC (no auth) - the patient join page asks this before loading video. Returns only timing state, no PHI.
     * Time is judged server-side against MySQL NOW() (same local clock the appointment times were stored in), so a
     * patient can't bypass the gate by changing their device clock. A logged-in staff session is never gated.
     */
    public function joinCheck(): void {
        header('Content-Type: application/json');
        header('Cache-Control: no-store');
        $room = trim($_GET['room'] ?? '');
        $session = $room !== '' ? Database::fetch(
            "SELECT status, scheduled_start, scheduled_end, NOW() AS now_ts FROM telehealth_sessions WHERE room_name = ?", [$room]
        ) : null;

        if (!$session) {
            echo json_encode(['status' => 'success', 'state' => 'invalid']);
            return;
        }

        $staff = !empty($_SESSION['user_id']);
        $now = strtotime($session['now_ts']);
        $start = $session['scheduled_start'] ? strtotime($session['scheduled_start']) : null;
        $end = $session['scheduled_end'] ? strtotime($session['scheduled_end']) : null;
        $opensAt = $start ? $start - self::JOIN_EARLY_MINUTES * 60 : null;

        if (in_array($session['status'], ['Completed', 'Cancelled'], true) && !$staff) {
            $state = 'ended';
        } elseif ($staff || $start === null) {
            $state = 'open';
        } elseif ($end !== null && $now > $end) {
            $state = 'ended';
        } elseif ($now < $opensAt) {
            $state = 'too_early';
        } else {
            $state = 'open';
        }

        echo json_encode([
            'status' => 'success',
            'state' => $state,
            'staff' => $staff,
            'early_minutes' => self::JOIN_EARLY_MINUTES,
            'starts_at' => $start ? date('Y-m-d H:i:s', $start) : null,
            'opens_at' => $opensAt ? date('Y-m-d H:i:s', $opensAt) : null,
            'seconds_until_open' => $opensAt ? max(0, $opensAt - $now) : 0,
        ]);
    }

    private function sendTelehealthEmail(string $patientName, string $patientEmail, string $joinUrl, string $roomName, ?string $startTime = null, bool $rescheduled = false): bool {
        $subject = $rescheduled ? "Updated: Specialty EHR Telehealth Consultation Time" : "Specialty EHR Telehealth Consultation Link";
        
        // Link to our join page (not meet.jit.si directly) so the 5-minute early-join gate is enforced
        $effectiveUrl = $joinUrl;
        $whenHtml = '';
        if ($startTime) {
            $when = date('l, F j, Y \a\t g:i A', strtotime($startTime));
            $whenHtml = '<p style="margin: 4px 0; font-size: 14px; color: #334155;"><strong>Appointment:</strong> ' . htmlspecialchars($when, ENT_QUOTES, 'UTF-8') . '</p>'
                . '<p style="margin: 4px 0; font-size: 14px; color: #334155;"><strong>Joining:</strong> The link opens ' . self::JOIN_EARLY_MINUTES . ' minutes before your appointment time.</p>';
        }

        $escapedName = htmlspecialchars($patientName, ENT_QUOTES, 'UTF-8');
        $escapedUrl = htmlspecialchars($effectiveUrl, ENT_QUOTES, 'UTF-8');

        $bodyHtml = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Specialty EHR Video Consultation</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; color: #333333;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); overflow: hidden; border: 1px solid #e2e8f0;">
        <tr>
            <td style="background-color: #0284c7; padding: 24px; text-align: center; color: #ffffff;">
                <h1 style="margin: 0; font-size: 24px; font-weight: bold;">Specialty EHR</h1>
                <p style="margin: 6px 0 0 0; font-size: 14px; opacity: 0.9;">Secure Telehealth Consultation Link</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px 24px;">
                <p style="font-size: 16px; margin-top: 0;">Dear <strong>' . $escapedName . '</strong>,</p>
                <p style="font-size: 15px; line-height: 1.6; color: #475569;">
                    Your healthcare provider at Specialty EHR has invited you to join a secure HIPAA-compliant video consultation.
                </p>
                
                <div style="background-color: #f0f9ff; border-left: 4px solid #0284c7; padding: 16px; border-radius: 4px; margin: 24px 0;">
                    <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #0369a1;">Consultation Details:</p>
                    <p style="margin: 4px 0; font-size: 14px; color: #334155;"><strong>Patient:</strong> ' . $escapedName . '</p>' . $whenHtml . '
                    <p style="margin: 4px 0; font-size: 14px; color: #334155;"><strong>Security Status:</strong> Encrypted Peer-to-Peer Video</p>
                </div>

                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $escapedUrl . '" target="_blank" style="background-color: #0d9488; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; padding: 14px 28px; border-radius: 6px; display: inline-block; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        Join Video Consultation
                    </a>
                </div>

                <p style="font-size: 14px; color: #64748b; line-height: 1.5;">
                    If the button above does not work, copy and paste the following link into your browser:<br>
                    <a href="' . $escapedUrl . '" style="color: #0284c7; word-break: break-all;">' . $escapedUrl . '</a>
                </p>

                <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">

                <h4 style="margin: 0 0 10px 0; font-size: 14px; color: #1e293b;">Tips for a successful consultation:</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #475569; line-height: 1.6;">
                    <li>Ensure you are in a quiet, well-lit private area.</li>
                    <li>Allow browser access to your camera and microphone when prompted.</li>
                    <li>Use Chrome, Firefox, Safari, or Edge for the best experience.</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f8fafc; padding: 16px 24px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                This is an automated message from Specialty EHR. Please do not reply directly to this email.
            </td>
        </tr>
    </table>
</body>
</html>';

        return EmailService::send($patientEmail, $subject, $bodyHtml, 'Specialty EHR Telehealth');
    }
}
