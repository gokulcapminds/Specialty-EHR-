<?php

namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EncryptionService;
use App\Services\EmailService;

class RecallController {
    private function checkAccess(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
            exit;
        }
    }

    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'Doctor';

        $where = [];
        $params = [];

        // Scope to logged in provider unless Super Admin/Staff
        if (!in_array($userRole, ['Super Admin', 'Admin', 'Staff', 'Billing Staff'])) {
            $where[] = "(r.provider_id = ? OR r.provider_id IS NULL)";
            $params[] = $userId;
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT r.*, 
                       p.first_name_encrypted, p.last_name_encrypted, p.phone_encrypted, p.email,
                       CONCAT(u.first_name, ' ', u.last_name) AS provider_name,
                       COALESCE(att.attempts_count, 0) AS attempts_count,
                       att.last_outcome,
                       att.last_channel
                FROM patient_recalls r
                JOIN patients p ON r.patient_id = p.id
                LEFT JOIN users u ON r.provider_id = u.id
                LEFT JOIN (
                    SELECT a1.recall_id,
                           cnt.attempts_count,
                           a1.outcome AS last_outcome,
                           a1.channel AS last_channel
                    FROM patient_recall_attempts a1
                    INNER JOIN (
                        SELECT recall_id, MAX(id) AS max_id, COUNT(*) AS attempts_count
                        FROM patient_recall_attempts
                        GROUP BY recall_id
                    ) cnt ON a1.id = cnt.max_id
                ) att ON att.recall_id = r.id
                {$whereSql}
                ORDER BY r.target_date ASC, r.id DESC";

        $rows = Database::fetchAll($sql, $params);

        $recalls = array_map(function($r) {
            $r['patient_first_name'] = !empty($r['first_name_encrypted']) ? EncryptionService::decrypt($r['first_name_encrypted']) : '';
            $r['patient_last_name'] = !empty($r['last_name_encrypted']) ? EncryptionService::decrypt($r['last_name_encrypted']) : '';
            $r['patient_phone'] = !empty($r['phone_encrypted']) ? EncryptionService::decrypt($r['phone_encrypted']) : '';
            unset($r['first_name_encrypted'], $r['last_name_encrypted'], $r['phone_encrypted']);
            return $r;
        }, $rows);

        // Calculate KPI Stats
        $today = date('Y-m-d');
        $thisWeekStart = date('Y-m-d', strtotime('monday this week'));
        $thisWeekEnd = date('Y-m-d', strtotime('sunday this week'));
        $thisMonth = date('Y-m');

        $dueThisWeek = 0;
        $overdue = 0;
        $scheduledThisMonth = 0;
        $completedCount = 0;
        $totalCount = count($recalls);

        foreach ($recalls as $rc) {
            $status = $rc['status'];
            $tDate = $rc['target_date'];

            if ($status === 'Pending' && $tDate >= $thisWeekStart && $tDate <= $thisWeekEnd) {
                $dueThisWeek++;
            }
            if ($status === 'Pending' && $tDate < $today) {
                $overdue++;
            }
            if ($status === 'Pending' && $tDate >= $today) {
                $scheduledThisMonth++;
            }
            if ($status === 'Completed') {
                $completedCount++;
            }
        }

        $conversionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        echo json_encode([
            'status' => 'success',
            'data' => $recalls,
            'kpis' => [
                'due_this_week' => $dueThisWeek,
                'overdue' => $overdue,
                'scheduled_this_month' => $scheduledThisMonth,
                'conversion_rate' => $conversionRate
            ]
        ]);
    }

    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $recalls = Database::fetchAll(
            "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM patient_recalls r
             LEFT JOIN users u ON r.provider_id = u.id
             WHERE r.patient_id = ?
             ORDER BY r.target_date ASC, r.id DESC",
            [$patientId]
        );

        echo json_encode(['status' => 'success', 'data' => $recalls]);
    }

    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $raw = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $patientId = $raw['patient_id'] ?? null;
        $recallType = $raw['recall_type'] ?? 'Hygiene';
        $targetDate = $raw['target_date'] ?? date('Y-m-d', strtotime('+1 month'));
        $priority = $raw['priority'] ?? 'Normal';
        $internalNote = $raw['internal_note'] ?? null;
        $providerId = $_SESSION['user_id'] ?? 1;

        if (!$patientId || empty($recallType) || empty($targetDate)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID, Recall Type, and Target Date are required.']);
            return;
        }

        $sql = "INSERT INTO patient_recalls 
                (patient_id, recall_type, target_date, priority, internal_note, provider_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending', NOW())";

        Database::query($sql, [$patientId, $recallType, $targetDate, $priority, $internalNote, $providerId]);
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, "Create Recall ID: {$newId} ({$recallType})", 'Recalls');

        echo json_encode(['status' => 'success', 'message' => 'Recall set successfully!', 'id' => $newId]);
    }

    public function updateStatus(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $raw = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $status = $raw['status'] ?? 'Completed';

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall ID required.']);
            return;
        }

        Database::query("UPDATE patient_recalls SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

        $rec = Database::fetch("SELECT r.patient_id, r.recall_type, p.first_name_encrypted, p.email FROM patient_recalls r JOIN patients p ON r.patient_id = p.id WHERE r.id = ?", [$id]);
        $emailSent = false;
        if ($rec) {
            if (!empty($rec['email']) && $status === 'Completed') {
                $firstName = !empty($rec['first_name_encrypted']) ? EncryptionService::decrypt($rec['first_name_encrypted']) : 'Patient';
                $rType = htmlspecialchars($rec['recall_type']);

                $bodyHtml = "
                    <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                        <h2 style='color: #166534; margin-top: 0;'>Recall Follow-Up Completed</h2>
                        <p style='color: #334155;'>Dear {$firstName},</p>
                        <p style='color: #475569;'>Your <strong>{$rType}</strong> recall follow-up has been officially marked as <strong>COMPLETED</strong>.</p>
                        <p style='color: #475569; font-size: 0.9rem;'>Thank you for choosing Specialty EHR!</p>
                        <p style='font-size: 0.85rem; color: #94a3b8; margin-bottom: 0;'>Specialty EHR Workspace • HIPAA Compliant Patient Portal</p>
                    </div>
                ";

                $emailSent = EmailService::send($rec['email'], "Recall Completed Notice - {$rType}", $bodyHtml, "Specialty EHR");
            }
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $rec['patient_id'], "Update Recall ID: {$id} Status: {$status} (Email Sent: " . ($emailSent ? 'Yes' : 'No') . ")", 'Recalls');
        }

        echo json_encode(['status' => 'success', 'message' => "Recall status updated to {$status}.", 'email_sent' => $emailSent]);
    }

    public function logAttempt(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $raw = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $channel = $raw['channel'] ?? 'Phone';
        $outcome = $raw['outcome'] ?? 'No answer';
        $note = $raw['note'] ?? '';

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall ID required.']);
            return;
        }

        $rec = Database::fetch("SELECT r.patient_id, r.recall_type, r.target_date, p.first_name_encrypted, p.email FROM patient_recalls r JOIN patients p ON r.patient_id = p.id WHERE r.id = ?", [$id]);
        if (!$rec) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Recall record not found.']);
            return;
        }

        $sql = "INSERT INTO patient_recall_attempts (recall_id, patient_id, channel, outcome, note, logged_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        Database::query($sql, [$id, $rec['patient_id'], $channel, $outcome, $note, $_SESSION['user_id']]);

        $emailSent = false;
        if (($channel === 'Email' || !empty($raw['send_email'])) && !empty($rec['email'])) {
            $firstName = !empty($rec['first_name_encrypted']) ? EncryptionService::decrypt($rec['first_name_encrypted']) : 'Patient';
            $rType = htmlspecialchars($rec['recall_type']);
            $tDate = htmlspecialchars($rec['target_date']);
            $noteText = htmlspecialchars($note);

            $bodyHtml = "
                <div style='font-family: system-ui, -apple-system, sans-serif; max-width: 600px; padding: 24px; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;'>
                    <h2 style='color: #0284c7; margin-top: 0;'>Recall Follow-Up Notice</h2>
                    <p style='color: #334155;'>Dear {$firstName},</p>
                    <p style='color: #475569;'>This is a follow-up regarding your upcoming <strong>{$rType}</strong> recall scheduled for <strong>{$tDate}</strong>.</p>
                    " . ($noteText ? "<div style='background-color: #f8fafc; padding: 14px; border-left: 3px solid #cbd5e1; color: #475569; margin: 14px 0; border-radius: 4px;'><strong>Clinic Note:</strong> {$noteText}</div>" : "") . "
                    <div style='background-color: #f0f9ff; padding: 16px; border-left: 4px solid #0284c7; color: #0369a1; margin: 16px 0; border-radius: 4px;'>
                        <strong>Action Required:</strong><br>
                        Please contact our clinic to schedule your appointment.
                    </div>
                    <p style='font-size: 0.85rem; color: #94a3b8; margin-bottom: 0;'>Specialty EHR Workspace • HIPAA Compliant Patient Portal</p>
                </div>
            ";

            $emailSent = EmailService::send($rec['email'], "Recall Follow-up Notice - {$rType}", $bodyHtml, "Specialty EHR");
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $rec['patient_id'], "Log Recall Attempt: Channel={$channel}, Outcome={$outcome} (Email Sent: " . ($emailSent ? 'Yes' : 'No') . ")", 'Recalls');

        echo json_encode(['status' => 'success', 'message' => $emailSent ? 'Contact attempt logged & email sent to patient!' : 'Contact attempt logged successfully!', 'email_sent' => $emailSent]);
    }

    public function snooze(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $raw = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $newTargetDate = $raw['target_date'] ?? null;

        if (!$id || !$newTargetDate) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall ID and new target date are required.']);
            return;
        }

        Database::query("UPDATE patient_recalls SET target_date = ?, updated_at = NOW() WHERE id = ?", [$newTargetDate, $id]);

        $rec = Database::fetch("SELECT patient_id FROM patient_recalls WHERE id = ?", [$id]);
        if ($rec) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $rec['patient_id'], "Snooze Recall ID: {$id} to {$newTargetDate}", 'Recalls');
        }

        echo json_encode(['status' => 'success', 'message' => "Recall snoozed to {$newTargetDate}."]);
    }

    public function delete(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall ID required.']);
            return;
        }

        $rec = Database::fetch("SELECT patient_id, recall_type FROM patient_recalls WHERE id = ?", [$id]);
        if ($rec) {
            Database::query("DELETE FROM patient_recalls WHERE id = ?", [$id]);
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $rec['patient_id'], "Delete Recall ID: {$id}", 'Recalls');
        }

        echo json_encode(['status' => 'success', 'message' => 'Recall removed successfully.']);
    }

    public function update(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $raw = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall ID required.']);
            return;
        }

        $recallType = $raw['recall_type'] ?? null;
        $targetDate = $raw['target_date'] ?? null;
        $priority = $raw['priority'] ?? 'Normal';
        $internalNote = $raw['internal_note'] ?? null;
        $providerId = !empty($raw['provider_id']) ? $raw['provider_id'] : null;
        $status = $raw['status'] ?? 'Pending';

        if (empty($recallType) || empty($targetDate)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Recall Type and Target Date are required.']);
            return;
        }

        $sql = "UPDATE patient_recalls 
                SET recall_type = ?, target_date = ?, priority = ?, internal_note = ?, status = ?, updated_at = NOW()";
        $binds = [$recallType, $targetDate, $priority, $internalNote, $status];

        if ($providerId !== null) {
            $sql .= ", provider_id = ?";
            $binds[] = $providerId;
        }
        $sql .= " WHERE id = ?";
        $binds[] = $id;

        Database::query($sql, $binds);

        $rec = Database::fetch("SELECT patient_id FROM patient_recalls WHERE id = ?", [$id]);
        if ($rec) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $rec['patient_id'], "Update Recall ID: {$id} ({$recallType})", 'Recalls');
        }

        echo json_encode(['status' => 'success', 'message' => 'Recall updated successfully!']);
    }

    public function exportCsv(): void {
        $this->checkAccess();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="recalls_call_list_' . date('Y-m-d') . '.csv"');

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'Doctor';

        $where = [];
        $params = [];
        if (!in_array($userRole, ['Super Admin', 'Admin', 'Staff', 'Billing Staff'])) {
            $where[] = "(r.provider_id = ? OR r.provider_id IS NULL)";
            $params[] = $userId;
        }
        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $rows = Database::fetchAll("
            SELECT r.id, r.recall_type, r.target_date, r.priority, r.status, r.internal_note,
                   p.first_name_encrypted, p.last_name_encrypted, p.phone_encrypted, p.email,
                   CONCAT(u.first_name, ' ', u.last_name) AS provider_name
            FROM patient_recalls r
            JOIN patients p ON r.patient_id = p.id
            LEFT JOIN users u ON r.provider_id = u.id
            {$whereSql}
            ORDER BY r.target_date ASC
        ", $params);

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Recall ID', 'Patient Name', 'Phone', 'Email', 'Recall Type', 'Target Date', 'Priority', 'Status', 'Provider', 'Note']);

        foreach ($rows as $r) {
            $fn = !empty($r['first_name_encrypted']) ? EncryptionService::decrypt($r['first_name_encrypted']) : '';
            $ln = !empty($r['last_name_encrypted']) ? EncryptionService::decrypt($r['last_name_encrypted']) : '';
            $phone = !empty($r['phone_encrypted']) ? EncryptionService::decrypt($r['phone_encrypted']) : '';

            fputcsv($out, [
                'RCL-' . str_pad($r['id'], 3, '0', STR_PAD_LEFT),
                $fn . ' ' . $ln,
                $phone,
                $r['email'],
                $r['recall_type'],
                $r['target_date'],
                $r['priority'],
                $r['status'],
                $r['provider_name'],
                $r['internal_note']
            ]);
        }

        fclose($out);
        exit;
    }
}
