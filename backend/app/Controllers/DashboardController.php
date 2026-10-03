<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\EncryptionService;

/**
 * GET /api/dashboard/summary - everything the main dashboard shows, in one round-trip, computed with SQL counts
 * (the old dashboard downloaded and decrypted the whole patient list just to count it).
 *
 * "Today" always comes from MySQL CURDATE(): PHP's timezone is UTC on this server while appointment times are
 * stored in the local clock (see the Telehealth conventions in CLAUDE.md).
 * Role scoping lives here, not in the UI: blocks a role can't use are omitted from the response.
 */
class DashboardController {
    private const CLINICIANS = ['Doctor', 'Nurse'];
    private const CLINICAL_ROLES = ['Super Admin', 'Doctor', 'Nurse', 'Receptionist'];
    private const BILLING_ROLES = ['Super Admin', 'Doctor', 'Billing Staff'];   // same as BillingController::unbilledEncounters
    private const NO_REFERRAL_RECALL_ROLES = ['Billing Staff'];
    private const CHECKED_IN = ['Arrived', 'Checked-In', 'In Room', 'In-Progress'];   // EncounterController::CHECKED_IN
    private const SKIP_STATUSES = ['Cancelled', 'No Show', 'Waiting List'];
    private const OPEN_INVOICE = ['Issued', 'Partially Paid', 'Overdue', 'Patient Balance'];

    private function dec($v): string {
        if ($v === null || $v === '') return '';
        try { return (string)EncryptionService::decrypt($v); } catch (\Throwable $e) { return ''; }
    }

    private function count(string $sql, array $params = []): int {
        $row = Database::fetch($sql, $params);
        return (int)($row['c'] ?? 0);
    }

    private function in(array $values): string {
        return implode(',', array_fill(0, count($values), '?'));
    }

    public function summary(): void {
        header('Content-Type: application/json');
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $role = $_SESSION['user_role'] ?? '';
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.']);
            return;
        }

        $isClinician = in_array($role, self::CLINICIANS, true);
        $canClinical = in_array($role, self::CLINICAL_ROLES, true);
        $canBilling = in_array($role, self::BILLING_ROLES, true);
        $canReferralsRecalls = !in_array($role, self::NO_REFERRAL_RECALL_ROLES, true);

        // Clinicians see their own schedule / notes; admins and front desk see everything
        $apptScope = $isClinician ? ' AND a.provider_id = ?' : '';
        $apptScopeParams = $isClinician ? [$userId] : [];
        $noteScope = $isClinician ? ' AND n.provider_id = ?' : '';
        $noteScopeParams = $isClinician ? [$userId] : [];

        $skip = $this->in(self::SKIP_STATUSES);
        $checked = $this->in(self::CHECKED_IN);

        // ---- KPIs ----
        $kpis = [
            'total_patients' => $this->count("SELECT COUNT(*) c FROM patients WHERE patient_status <> 'Draft'"),
            'appointments_today' => $this->count(
                "SELECT COUNT(*) c FROM appointments a WHERE DATE(a.start_time) = CURDATE() AND a.status NOT IN ($skip) AND COALESCE(a.category, '') <> 'Waiting List'" . $apptScope,
                array_merge(self::SKIP_STATUSES, $apptScopeParams)
            ),
            'arrived_today' => $this->count(
                "SELECT COUNT(*) c FROM appointments a WHERE DATE(a.start_time) = CURDATE() AND a.status IN ($checked)" . $apptScope,
                array_merge(self::CHECKED_IN, $apptScopeParams)
            ),
        ];

        // ---- Patient overview ----
        $overview = [
            'active' => $this->count("SELECT COUNT(*) c FROM patients WHERE patient_status = 'Active'"),
            'inactive' => $this->count("SELECT COUNT(*) c FROM patients WHERE patient_status = 'Inactive'"),
            'new_30d' => $this->count("SELECT COUNT(*) c FROM patients WHERE patient_status <> 'Draft' AND DATE(created_at) >= CURDATE() - INTERVAL 29 DAY"),
            'follow_ups_due' => null,
        ];

        // ---- Needs attention (same definitions as the module each row links to) ----
        $attention = [];
        if ($canClinical) {
            $attention[] = ['key' => 'notes_to_sign', 'label' => 'Clinical notes waiting for signature', 'icon' => 'fa-file-signature', 'link' => '#encounters',
                'count' => $this->count("SELECT COUNT(*) c FROM clinical_notes n WHERE n.lock_state = 0 AND n.encounter_status = 'ready_for_sign'" . $noteScope, $noteScopeParams)];
        }
        if ($canReferralsRecalls) {
            // same scoping as RecallController::all - everyone but Super Admin sees their own (or unassigned) recalls
            $recallScope = $role === 'Super Admin' ? '' : ' AND (r.provider_id = ? OR r.provider_id IS NULL)';
            $recallParams = $recallScope === '' ? [] : [$userId];
            $overdue = $this->count("SELECT COUNT(*) c FROM patient_recalls r WHERE r.status = 'Pending' AND r.target_date < CURDATE()" . $recallScope, $recallParams);
            $due = $this->count("SELECT COUNT(*) c FROM patient_recalls r WHERE r.status = 'Pending' AND r.target_date <= CURDATE()" . $recallScope, $recallParams);
            $overview['follow_ups_due'] = $due;
            $attention[] = ['key' => 'overdue_recalls', 'label' => 'Overdue patient recalls', 'icon' => 'fa-clock-rotate-left', 'link' => '#recalls', 'count' => $overdue];
            $attention[] = ['key' => 'pending_referrals', 'label' => 'Referrals pending', 'icon' => 'fa-user-md', 'link' => '#referrals',
                'count' => $this->count("SELECT COUNT(*) c FROM patient_referrals WHERE status = 'Pending'")];
        }
        $attention[] = ['key' => 'unread_messages', 'label' => 'Unread messages', 'icon' => 'fa-comments', 'link' => '#messaging',
            'count' => $this->count("SELECT COUNT(*) c FROM message_recipients mr JOIN secure_messages m ON m.id = mr.message_id WHERE mr.receiver_id = ? AND mr.read_at IS NULL", [$userId])];

        // ---- Billing (only roles that can open Billing) ----
        $billing = null;
        if ($canBilling) {
            $unbilled = $this->count("SELECT COUNT(*) c FROM clinical_notes cn WHERE cn.lock_state = 1 AND cn.id NOT IN (SELECT encounter_id FROM invoices WHERE encounter_id IS NOT NULL)");
            $overdueInv = $this->count(
                "SELECT COUNT(*) c FROM invoices WHERE due_date IS NOT NULL AND due_date < CURDATE() AND status IN (" . $this->in(self::OPEN_INVOICE) . ")",
                self::OPEN_INVOICE
            );
            $badClaims = $this->count("SELECT COUNT(*) c FROM insurance_claims WHERE status IN ('Rejected', 'Denied')");
            $billing = ['unbilled_encounters' => $unbilled, 'overdue_invoices' => $overdueInv, 'rejected_claims' => $badClaims, 'attention' => $unbilled + $overdueInv + $badClaims];
            $kpis['billing_attention'] = $billing['attention'];
            $attention[] = ['key' => 'unbilled_encounters', 'label' => 'Signed encounters not yet billed', 'icon' => 'fa-file-invoice-dollar', 'link' => '#billing', 'count' => $unbilled];
            $attention[] = ['key' => 'overdue_invoices', 'label' => 'Overdue invoices', 'icon' => 'fa-hourglass-half', 'link' => '#billing', 'count' => $overdueInv];
            $attention[] = ['key' => 'rejected_claims', 'label' => 'Claims rejected or denied', 'icon' => 'fa-file-circle-exclamation', 'link' => '#billing', 'count' => $badClaims];
        }

        // ---- Today's schedule (the only block that decrypts PHI) ----
        $schedule = [];
        if ($role !== 'Billing Staff') {
            $rows = Database::fetchAll(
                "SELECT a.id, a.patient_id, a.start_time, a.status, a.visit_type, a.appointment_mode, a.provider_id,
                        p.first_name_encrypted, p.last_name_encrypted,
                        CONCAT(u.first_name, ' ', u.last_name) AS provider_name,
                        (SELECT MIN(n.id) FROM clinical_notes n WHERE n.appointment_id = a.id) AS note_id
                 FROM appointments a
                 JOIN patients p ON p.id = a.patient_id
                 LEFT JOIN users u ON u.id = a.provider_id
                 WHERE DATE(a.start_time) = CURDATE() AND COALESCE(a.category, '') <> 'Waiting List'" . $apptScope . "
                 ORDER BY a.start_time ASC LIMIT 50",
                $apptScopeParams
            );
            foreach ($rows as $r) {
                $schedule[] = [
                    'appointment_id' => (int)$r['id'],
                    'patient_id' => (int)$r['patient_id'],
                    'patient_name' => trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted'])),
                    'mrn' => 'MRN-' . str_pad((string)$r['patient_id'], 6, '0', STR_PAD_LEFT),
                    'provider_name' => trim((string)$r['provider_name']),
                    'start_time' => $r['start_time'],
                    'visit_type' => $r['visit_type'] ?: ($r['appointment_mode'] ?: 'Visit'),
                    'status' => $r['status'] ?: 'Scheduled',
                    'note_id' => $r['note_id'] ? (int)$r['note_id'] : null,
                ];
            }
        }

        // ---- Practice activity: last 30 days, zero-filled ----
        $today = Database::fetch("SELECT CURDATE() AS d")['d'];
        $series = [
            'completed_encounters' => $canClinical ? "SELECT DATE(COALESCE(n.signed_at, n.locked_at, n.note_date)) d, COUNT(*) c FROM clinical_notes n WHERE n.lock_state = 1 AND DATE(COALESCE(n.signed_at, n.locked_at, n.note_date)) >= CURDATE() - INTERVAL 29 DAY" . $noteScope . " GROUP BY d" : null,
            'new_patients' => "SELECT DATE(created_at) d, COUNT(*) c FROM patients WHERE patient_status <> 'Draft' AND DATE(created_at) >= CURDATE() - INTERVAL 29 DAY GROUP BY d",
            'no_shows_cancellations' => "SELECT DATE(a.start_time) d, COUNT(*) c FROM appointments a WHERE a.status IN ('No Show', 'Cancelled') AND DATE(a.start_time) >= CURDATE() - INTERVAL 29 DAY AND DATE(a.start_time) <= CURDATE()" . $apptScope . " GROUP BY d",
            'pending_clinical_docs' => $canClinical ? "SELECT DATE(n.note_date) d, COUNT(*) c FROM clinical_notes n WHERE n.lock_state = 0 AND DATE(n.note_date) >= CURDATE() - INTERVAL 29 DAY" . $noteScope . " GROUP BY d" : null,
            'billing_claims' => $canBilling ? "SELECT d, SUM(c) c FROM (SELECT DATE(created_at) d, COUNT(*) c FROM invoices WHERE DATE(created_at) >= CURDATE() - INTERVAL 29 DAY GROUP BY d UNION ALL SELECT DATE(created_at) d, COUNT(*) c FROM insurance_claims WHERE DATE(created_at) >= CURDATE() - INTERVAL 29 DAY GROUP BY d) x GROUP BY d" : null,
        ];
        $paramsFor = [
            'completed_encounters' => $noteScopeParams, 'new_patients' => [], 'no_shows_cancellations' => $apptScopeParams,
            'pending_clinical_docs' => $noteScopeParams, 'billing_claims' => [],
        ];
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $days[date('Y-m-d', strtotime($today . " -$i day"))] = array_fill_keys(array_keys($series), 0);
        }
        foreach ($series as $key => $sql) {
            if ($sql === null) continue;
            foreach (Database::fetchAll($sql, $paramsFor[$key]) as $r) {
                if (isset($days[$r['d']])) $days[$r['d']][$key] = (int)$r['c'];
            }
        }
        $activity = [];
        foreach ($days as $d => $vals) $activity[] = array_merge(['date' => $d], $vals);

        echo json_encode([
            'status' => 'success',
            'today' => $today,
            'role' => $role,
            'scope' => $isClinician ? 'own' : 'all',
            'kpis' => $kpis,
            'overview' => $overview,
            'needs_attention' => ['items' => count(array_filter($attention, fn($a) => $a['count'] > 0)), 'rows' => $attention],
            'billing' => $billing,
            'today_schedule' => $schedule,
            'activity' => ['series' => array_keys(array_filter($series)), 'days' => $activity],
        ]);
    }

    private function plainComplaint($raw): string {
        $raw = trim((string)$raw);
        if ($raw !== '' && $raw[0] === '{') {
            $j = json_decode($raw, true);
            if (is_array($j)) $raw = trim((string)($j['text'] ?? ''));
        }
        return mb_substr($raw, 0, 160);
    }

    /**
     * GET /api/patient/{id}/dashboard - the patient chart's Dashboard tab in one round-trip.
     * Role scoping lives here: clinical blocks only for Super Admin/Doctor/Nurse (Receptionist and
     * Billing Staff never get notes/diagnoses/meds/allergies/vitals), billing only for Super Admin/Doctor/Billing Staff.
     */
    public function patient(array $params): void {
        header('Content-Type: application/json');
        $role = $_SESSION['user_role'] ?? '';
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $patientId = (int)($params['id'] ?? $params['patient_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.']);
            return;
        }
        if (!$patientId || !Database::fetch("SELECT id FROM patients WHERE id = ?", [$patientId])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $clinical = in_array($role, Roles::CLINICAL, true);
        $billing = in_array($role, self::BILLING_ROLES, true);
        $recalls = $role !== 'Billing Staff';
        $skip = $this->in(self::SKIP_STATUSES);

        $out = [
            'status' => 'success', 'patient_id' => $patientId, 'role' => $role,
            'access' => ['clinical' => $clinical, 'billing' => $billing],
            'diagnoses' => [], 'diagnoses_total' => 0, 'medications' => [], 'medications_total' => 0,
            'allergies' => [], 'allergies_recorded' => false,
            'latest_vitals' => null, 'recent_vitals' => [], 'recent_notes' => [], 'latest_encounter' => null,
            'upcoming_appointments' => [], 'balance_due' => null, 'attention' => [],
        ];
        $attention = [];

        if ($clinical) {
            $out['diagnoses'] = Database::fetchAll(
                "SELECT id, icd10_code, description, chronicity FROM patient_problems WHERE patient_id = ? AND status = 'Active' ORDER BY created_at DESC, id DESC LIMIT 6", [$patientId]);
            $out['diagnoses_total'] = $this->count("SELECT COUNT(*) c FROM patient_problems WHERE patient_id = ? AND status = 'Active'", [$patientId]);
            $out['medications'] = Database::fetchAll(
                "SELECT id, medication_name, dosage, route, frequency FROM medications WHERE patient_id = ? AND status = 'Active' ORDER BY created_at DESC, id DESC LIMIT 6", [$patientId]);
            $out['medications_total'] = $this->count("SELECT COUNT(*) c FROM medications WHERE patient_id = ? AND status = 'Active'", [$patientId]);
            $out['allergies'] = Database::fetchAll(
                "SELECT id, allergen, reaction, severity FROM patient_allergies WHERE patient_id = ? AND status = 'Active' ORDER BY created_at DESC, id DESC LIMIT 8", [$patientId]);
            $out['allergies_recorded'] = $this->count("SELECT COUNT(*) c FROM patient_allergies WHERE patient_id = ?", [$patientId]) > 0;

            $notes = Database::fetchAll(
                "SELECT n.id, n.note_date, n.encounter_type, n.encounter_status, n.lock_state, n.chief_complaint, n.clinical_summary,
                        CONCAT(u.first_name, ' ', u.last_name) AS provider_name
                 FROM clinical_notes n LEFT JOIN users u ON u.id = n.provider_id
                 WHERE n.patient_id = ? ORDER BY n.note_date DESC, n.id DESC LIMIT 5", [$patientId]);
            foreach ($notes as $n) {
                $label = (int)$n['lock_state'] === 1 ? 'Signed' : ($n['encounter_status'] === 'ready_for_sign' ? 'Ready to sign' : 'Draft');
                $out['recent_notes'][] = [
                    'id' => (int)$n['id'], 'note_date' => $n['note_date'], 'type' => $n['encounter_type'] ?: 'Encounter',
                    'provider_name' => trim((string)$n['provider_name']), 'status' => $label,
                ];
            }
            if ($notes) {
                $n = $notes[0];
                $out['latest_encounter'] = [
                    'id' => (int)$n['id'], 'note_date' => $n['note_date'], 'type' => $n['encounter_type'] ?: 'Encounter',
                    'status' => $out['recent_notes'][0]['status'], 'chief_complaint' => $this->plainComplaint($n['chief_complaint']),
                    'summary' => mb_substr(trim((string)$n['clinical_summary']), 0, 280),
                ];
            }

            $vit = Database::fetchAll(
                "SELECT note_date, vital_bp_systolic AS sys, vital_bp_diastolic AS dia, vital_heart_rate AS hr, vital_spo2 AS spo2, vital_weight AS weight, vital_bmi AS bmi, vital_temp AS temp
                 FROM clinical_notes
                 WHERE patient_id = ? AND (vital_bp_systolic IS NOT NULL OR vital_heart_rate IS NOT NULL OR vital_spo2 IS NOT NULL OR vital_weight IS NOT NULL)
                 ORDER BY note_date DESC, id DESC LIMIT 3", [$patientId]);
            $out['recent_vitals'] = $vit;
            $out['latest_vitals'] = $vit[0] ?? null;

            $unsigned = $this->count("SELECT COUNT(*) c FROM clinical_notes WHERE patient_id = ? AND lock_state = 0", [$patientId]);
            if ($unsigned > 0) $attention[] = ['key' => 'unsigned_notes', 'severity' => 'warn', 'label' => $unsigned . ' unsigned clinical note' . ($unsigned === 1 ? '' : 's'), 'goto' => 'encounters'];
            if ($out['diagnoses_total'] === 0) $attention[] = ['key' => 'no_diagnosis', 'severity' => 'warn', 'label' => 'No active diagnosis recorded', 'goto' => 'diagnoses'];
            if (!$out['allergies_recorded']) $attention[] = ['key' => 'no_allergies', 'severity' => 'warn', 'label' => 'Allergies not recorded', 'goto' => 'allergies'];
        }

        $out['upcoming_appointments'] = Database::fetchAll(
            "SELECT a.id, a.start_time, a.status, a.visit_type, a.appointment_mode, CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM appointments a LEFT JOIN users u ON u.id = a.provider_id
             WHERE a.patient_id = ? AND a.start_time >= NOW() AND a.status NOT IN ($skip) AND COALESCE(a.category, '') <> 'Waiting List'
             ORDER BY a.start_time ASC LIMIT 4", array_merge([$patientId], self::SKIP_STATUSES));
        if (!$out['upcoming_appointments']) $attention[] = ['key' => 'no_upcoming', 'severity' => 'info', 'label' => 'No upcoming appointment', 'goto' => 'appointments'];

        if ($recalls) {
            $overdue = $this->count("SELECT COUNT(*) c FROM patient_recalls WHERE patient_id = ? AND status = 'Pending' AND target_date < CURDATE()", [$patientId]);
            if ($overdue > 0) $attention[] = ['key' => 'overdue_recall', 'severity' => 'warn', 'label' => $overdue . ' overdue recall' . ($overdue === 1 ? '' : 's'), 'goto' => 'recalls'];
        }

        if ($billing) {
            $row = Database::fetch(
                "SELECT COALESCE(SUM(i.total_amount - i.paid_amount - COALESCE((SELECT SUM(a.amount) FROM adjustments a WHERE a.invoice_id = i.id AND a.voided_at IS NULL), 0)), 0) AS due
                 FROM invoices i WHERE i.patient_id = ? AND i.status IN ('Issued', 'Partially Paid', 'Patient Balance', 'Overdue')", [$patientId]);
            $due = round((float)($row['due'] ?? 0), 2);
            $out['balance_due'] = $due;
            if ($due > 0) $attention[] = ['key' => 'balance', 'severity' => 'warn', 'label' => 'Outstanding patient balance: $' . number_format($due, 2), 'goto' => 'billing'];
        }

        $out['attention'] = $attention;
        \App\Services\AuditLogger::log($userId, $_SESSION['username'] ?? '', $role, $patientId, 'View Patient Dashboard', 'Patient Chart');
        echo json_encode($out);
    }
}
