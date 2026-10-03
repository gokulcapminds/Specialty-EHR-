<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;
use App\Services\EncryptionService;
use App\Support\VisitTypes;

/**
 * Encounters module: one work queue over appointments + clinical notes, and "start encounter".
 * Everything else in the lifecycle reuses existing endpoints (appointment status, note status, sign & lock, addendum).
 *
 * Stage is DERIVED, never stored:
 *   Scheduled -> Checked in -> In progress -> Ready to sign -> Signed (lock_state = 1) -> Billed (an invoice exists)
 */
class EncounterController {
    private const STAGES = ['Scheduled', 'Checked in', 'In progress', 'Ready to sign', 'Signed', 'Billed'];
    // queue order: what needs action first
    private const RANK = ['Ready to sign' => 1, 'In progress' => 2, 'Checked in' => 3, 'Scheduled' => 4, 'Signed' => 5, 'Billed' => 6];
    private const VIEW_ROLES = Roles::ALL_STAFF;
    private const START_ROLES = Roles::CLINICAL;
    private const CHECKED_IN = ['Arrived', 'Checked-In', 'In Room', 'In-Progress'];

    private function checkAccess(array $roles): void {
        if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function dec($v): string {
        if ($v === null || $v === '') return '';
        try { return (string)EncryptionService::decrypt($v); } catch (\Throwable $e) { return ''; }
    }

    private function validDate(?string $d): bool {
        if ($d === null || $d === '') return false;
        $x = \DateTime::createFromFormat('Y-m-d', $d);
        return $x && $x->format('Y-m-d') === $d;
    }

    // GET /api/encounters/queue?range=today|week|all&provider_id=&stage=&q=
    public function queue(): void {
        $this->checkAccess(self::VIEW_ROLES);
        $role = $_SESSION['user_role'] ?? '';
        $range = in_array($_GET['range'] ?? '', ['today', 'week', 'all'], true) ? $_GET['range'] : 'today';
        $today = date('Y-m-d');
        if ($range === 'today') { $from = $today; $to = $today; }
        elseif ($range === 'week') { $from = date('Y-m-d', strtotime('monday this week')); $to = date('Y-m-d', strtotime('sunday this week')); }
        else { $from = '2000-01-01'; $to = '2100-01-01'; }
        $providerId = (int)($_GET['provider_id'] ?? 0);
        $q = strtolower(trim((string)($_GET['q'] ?? '')));

        $rows = [];

        // (A) appointments in the window that have no encounter yet
        $apptSql = "SELECT a.id AS appointment_id, a.status AS appointment_status, a.start_time, a.visit_type, a.specialty,
                           a.patient_id, a.provider_id, p.first_name_encrypted, p.last_name_encrypted, u.first_name AS pf, u.last_name AS pl
                    FROM appointments a
                    JOIN patients p ON p.id = a.patient_id
                    LEFT JOIN users u ON u.id = a.provider_id
                    WHERE DATE(a.start_time) BETWEEN ? AND ?
                      AND a.status NOT IN ('Cancelled', 'No Show', 'Waiting List', 'Completed')
                      AND NOT EXISTS (SELECT 1 FROM clinical_notes n WHERE n.appointment_id = a.id)";
        $apptParams = [$from, $to];
        if ($providerId) { $apptSql .= " AND a.provider_id = ?"; $apptParams[] = $providerId; }
        foreach (Database::fetchAll($apptSql, $apptParams) as $a) {
            $stage = in_array($a['appointment_status'], self::CHECKED_IN, true) ? 'Checked in' : 'Scheduled';
            $rows[] = [
                'key' => 'a' . $a['appointment_id'], 'stage' => $stage,
                'appointment_id' => (int)$a['appointment_id'], 'appointment_status' => $a['appointment_status'],
                'note_id' => null, 'encounter_ref' => null,
                'patient_id' => (int)$a['patient_id'], 'patient_name' => trim($this->dec($a['first_name_encrypted']) . ' ' . $this->dec($a['last_name_encrypted'])),
                'provider_id' => $a['provider_id'] ? (int)$a['provider_id'] : null, 'provider_name' => trim(($a['pf'] ?? '') . ' ' . ($a['pl'] ?? '')),
                'visit_type' => $a['visit_type'], 'specialty' => $a['specialty'],
                'when' => $a['start_time'], 'chief_complaint' => null, 'signed_by' => null, 'invoice_id' => null,
            ];
        }

        // (B) encounters: everything still open (any date) + signed/billed ones inside the window
        $noteSql = "SELECT n.id, n.patient_id, n.appointment_id, n.provider_id, n.note_date, n.visit_type, n.encounter_type, n.encounter_status,
                           n.lock_state, n.chief_complaint, n.signed_by_name, a.status AS appointment_status,
                           p.first_name_encrypted, p.last_name_encrypted, u.first_name AS pf, u.last_name AS pl,
                           (SELECT MIN(i.id) FROM invoices i WHERE i.encounter_id = n.id) AS invoice_id
                    FROM clinical_notes n
                    JOIN patients p ON p.id = n.patient_id
                    LEFT JOIN users u ON u.id = n.provider_id
                    LEFT JOIN appointments a ON a.id = n.appointment_id
                    WHERE (n.lock_state = 0 OR DATE(n.note_date) BETWEEN ? AND ?)";
        $noteParams = [$from, $to];
        if ($providerId) { $noteSql .= " AND n.provider_id = ?"; $noteParams[] = $providerId; }
        $noteSql .= " ORDER BY n.note_date DESC LIMIT 500";
        foreach (Database::fetchAll($noteSql, $noteParams) as $n) {
            if ((int)$n['lock_state'] === 1) $stage = $n['invoice_id'] ? 'Billed' : 'Signed';
            elseif ($n['encounter_status'] === 'ready_for_sign') $stage = 'Ready to sign';
            else $stage = 'In progress';
            $rows[] = [
                'key' => 'n' . $n['id'], 'stage' => $stage,
                'appointment_id' => $n['appointment_id'] ? (int)$n['appointment_id'] : null, 'appointment_status' => $n['appointment_status'],
                'note_id' => (int)$n['id'], 'encounter_ref' => 'ENC-' . str_pad((string)$n['id'], 5, '0', STR_PAD_LEFT),
                'patient_id' => (int)$n['patient_id'], 'patient_name' => trim($this->dec($n['first_name_encrypted']) . ' ' . $this->dec($n['last_name_encrypted'])),
                'provider_id' => $n['provider_id'] ? (int)$n['provider_id'] : null, 'provider_name' => trim(($n['pf'] ?? '') . ' ' . ($n['pl'] ?? '')),
                'visit_type' => $n['visit_type'], 'specialty' => $n['encounter_type'],
                'when' => $n['note_date'],
                // front desk sees the queue, not clinical content
                'chief_complaint' => in_array($role, [Roles::RECEPTIONIST, Roles::BILLING], true) ? null : ($n['chief_complaint'] ? mb_substr($n['chief_complaint'], 0, 90) : null),
                'signed_by' => $n['signed_by_name'], 'invoice_id' => $n['invoice_id'] ? (int)$n['invoice_id'] : null,
            ];
        }

        // Billing Staff only ever sees what has been signed
        if ($role === 'Billing Staff') $rows = array_values(array_filter($rows, fn($r) => in_array($r['stage'], ['Signed', 'Billed'], true)));
        if ($q !== '') {
            $rows = array_values(array_filter($rows, fn($r) => strpos(strtolower($r['patient_name'] . ' ' . ($r['encounter_ref'] ?? '') . ' ' . $r['provider_name']), $q) !== false));
        }

        $counts = array_fill_keys(self::STAGES, 0);
        foreach ($rows as $r) $counts[$r['stage']]++;
        $stage = (string)($_GET['stage'] ?? '');
        if ($stage !== '' && in_array($stage, self::STAGES, true)) $rows = array_values(array_filter($rows, fn($r) => $r['stage'] === $stage));

        usort($rows, function ($a, $b) {
            $ra = self::RANK[$a['stage']]; $rb = self::RANK[$b['stage']];
            return $ra !== $rb ? $ra <=> $rb : strcmp((string)$a['when'], (string)$b['when']);
        });
        $this->respond(['status' => 'success', 'data' => $rows, 'counts' => $counts, 'range' => $range, 'from' => $from, 'to' => $to]);
    }

    // POST /api/encounters/start   { appointment_id }  or  { patient_id, provider_id?, visit_type?, encounter_type? } (walk-in)
    public function start(): void {
        $this->checkAccess(self::START_ROLES);
        $in = json_decode(file_get_contents('php://input'), true) ?: [];
        $appointmentId = (int)($in['appointment_id'] ?? 0);
        $fail = fn(string $m, int $c = 400) => $this->respond(['status' => 'error', 'message' => $m], $c);

        if ($appointmentId) {
            $a = Database::fetch("SELECT * FROM appointments WHERE id = ?", [$appointmentId]);
            if (!$a) { $fail('Appointment not found.', 404); return; }
            // idempotent: never create a second encounter for the same appointment
            $existing = Database::fetch("SELECT id FROM clinical_notes WHERE appointment_id = ?", [$appointmentId]);
            if ($existing) { $this->respond(['status' => 'success', 'note_id' => (int)$existing['id'], 'existing' => true, 'message' => 'This visit already has an encounter.']); return; }
            if (in_array($a['status'], ['Cancelled', 'No Show', 'Waiting List', 'Completed'], true)) { $fail("This appointment is {$a['status']}."); return; }
            if (!in_array($a['status'], self::CHECKED_IN, true)) { $fail('Check the patient in first, then start the encounter.'); return; }
            $patientId = (int)$a['patient_id'];
            $providerId = (int)($a['provider_id'] ?: $_SESSION['user_id']);
            $noteDate = date('Y-m-d H:i:s', strtotime($a['start_time']));
            $visitType = $a['visit_type'] ?: VisitTypes::DEFAULT;      // the appointment's type (an old value is copied as it is)
            $modeMap = ['Telehealth' => 'Telehealth', 'Phone Call' => 'Phone', 'Phone' => 'Phone'];
            $mode = $modeMap[$a['appointment_mode'] ?? ''] ?? 'In-Person';
            $type = trim(preg_replace('/\s*EHR$/i', '', (string)($a['specialty'] ?? ''))) ?: 'Cardiology';
        } else {
            $patientId = (int)($in['patient_id'] ?? 0);
            if (!$patientId || !Database::fetch("SELECT id FROM patients WHERE id = ?", [$patientId])) { $fail('Choose a patient to start a walk-in encounter.'); return; }
            $providerId = (int)($in['provider_id'] ?? 0) ?: (int)$_SESSION['user_id'];
            if (!Database::fetch("SELECT id FROM users WHERE id = ? AND is_active = 1", [$providerId])) { $fail('Choose an active provider.'); return; }
            $noteDate = date('Y-m-d H:i:s');
            [$visitType, $visitTypeError] = VisitTypes::resolve($in['visit_type'] ?? null);
            if ($visitTypeError) { $fail($visitTypeError); return; }
            $mode = 'Walk-In';
            $type = trim(preg_replace('/\s*EHR$/i', '', (string)($in['encounter_type'] ?? ''))) ?: 'Cardiology';
        }

        Database::beginTransaction();
        try {
            Database::query(
                "INSERT INTO clinical_notes (patient_id, appointment_id, visit_type, encounter_mode, provider_id, note_date, encounter_type, encounter_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$patientId, $appointmentId ?: null, $visitType, $mode, $providerId, $noteDate, $type, 'in_progress']
            );
            $noteId = (int)Database::lastInsertId();
            if ($appointmentId) Database::query("UPDATE appointments SET status = 'In-Progress' WHERE id = ?", [$appointmentId]);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            // lost a race with another click: the unique key on appointment_id makes the second insert fail; return the winner
            if ($appointmentId && ($dup = Database::fetch("SELECT id FROM clinical_notes WHERE appointment_id = ?", [$appointmentId]))) {
                $this->respond(['status' => 'success', 'note_id' => (int)$dup['id'], 'existing' => true, 'message' => 'This visit already has an encounter.']);
                return;
            }
            $fail('Could not start the encounter.', 500);
            return;
        }
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Start Encounter' . ($appointmentId ? " from appointment #{$appointmentId}" : ' (walk-in)'), 'Clinical Workspace', (string)$noteId);
        $this->respond(['status' => 'success', 'note_id' => $noteId, 'existing' => false, 'message' => 'Encounter started.']);
    }
}
