<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;
use App\Services\AuditLogger;
use App\Services\Claim837Generator;
use App\Services\EdiStore;

/**
 * Insurance claim workflow: create from an invoice, readiness checks, status lifecycle, remittance posting,
 * denial/appeal, secondary billing, CMS-1500 data sheet and 837P export.
 * Money movement always goes through the payments/adjustments ledger + BillingController::recalcInvoice().
 */
class ClaimController {
    private const VIEW_ROLES = ['Super Admin', 'Billing Staff', 'Doctor'];
    private const EDIT_ROLES = ['Super Admin', 'Billing Staff'];
    private const OPEN_STATUSES = ['Draft', 'Ready', 'Submitted', 'Accepted', 'Rejected', 'Denied', 'Appealed'];
    // Manual transitions handled by POST /claims/{id}/status (remit/appeal/close/reverse have their own endpoints)
    private const TRANSITIONS = [
        'Draft'     => ['Ready'],
        'Ready'     => ['Submitted', 'Draft'],
        'Submitted' => ['Accepted', 'Rejected'],
        'Rejected'  => ['Ready'],
    ];
    private const TOL = 0.005;

    // ------------------------------------------------------------------ helpers
    private function checkAccess(array $roles): void {
        if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
            $this->respond(['status' => 'error', 'message' => 'Access forbidden.'], 403);
            exit();
        }
    }

    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function fail(string $message, int $code = 400, array $extra = []): void {
        $this->respond(array_merge(['status' => 'error', 'message' => $message], $extra), $code);
    }

    private function input(): array {
        $in = json_decode(file_get_contents('php://input'), true);
        return is_array($in) ? $in : [];
    }

    public function dec($v): string {
        if ($v === null || $v === '') return '';
        try { return (string)EncryptionService::decrypt($v); } catch (\Throwable $e) { return ''; }
    }

    public function audit(?int $patientId, string $action, int $recordId): void {
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, $action, 'Billing', (string)$recordId);
    }

    public function history(int $claimId, ?string $from, string $to, string $note = ''): void {
        Database::query(
            "INSERT INTO claim_status_history (claim_id, from_status, to_status, note, user_id) VALUES (?, ?, ?, ?, ?)",
            [$claimId, $from, $to, $note !== '' ? mb_substr($note, 0, 500) : null, $_SESSION['user_id']]
        );
    }

    public function validDate(?string $d): bool {
        if ($d === null || $d === '') return false;
        $x = \DateTime::createFromFormat('Y-m-d', $d);
        return $x && $x->format('Y-m-d') === $d;
    }

    private function money($v): float {
        return round((float)$v, 2);
    }

    public function settings(): array {
        $out = [];
        foreach (['billing_submitter_id', 'billing_receiver_id', 'billing_contact_name', 'billing_contact_phone'] as $k) {
            $r = Database::fetch("SELECT setting_value v FROM system_settings WHERE setting_key = ?", [$k]);
            $out[$k] = $r['v'] ?? '';
        }
        return $out;
    }

    // Facility-based data isolation: the single `JOIN invoices i ... JOIN patients p` here is the one place that
    // scopes every claim lookup (show/update/setStatus/remit/reverseRemit/appeal/close/billSecondary/edi837/cms1500
    // all load a claim through this), so a cross-facility claim id was never a real gap once this was filtered.
    public function load(int $id): ?array {
        $c = Database::fetch(
            "SELECT c.*, i.invoice_number, i.status AS invoice_status, i.total_amount AS invoice_total
             FROM insurance_claims c JOIN invoices i ON i.id = c.invoice_id JOIN patients p ON p.id = i.patient_id
             WHERE c.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        return $c ?: null;
    }

    public function claimLines(int $claimId): array {
        return Database::fetchAll("SELECT * FROM claim_lines WHERE claim_id = ? ORDER BY id", [$claimId]);
    }

    public function patientName(int $patientId): string {
        $p = Database::fetch("SELECT first_name_encrypted, last_name_encrypted FROM patients WHERE id = ?", [$patientId]);
        return $p ? trim($this->dec($p['first_name_encrypted']) . ' ' . $this->dec($p['last_name_encrypted'])) : '';
    }

    /** Normalized coverage snapshot from a patient_insurance row. */
    private function coverageFromRow(array $r): array {
        $first = trim((string)($r['subscriber_first_name'] ?? ''));
        $last = trim((string)($r['subscriber_last_name'] ?? ''));
        if ($first === '' && $last === '' && !empty($r['subscriber_name'])) {
            $parts = preg_split('/\s+/', trim($r['subscriber_name']), 2);
            $first = $parts[0] ?? '';
            $last = $parts[1] ?? '';
        }
        return [
            'payer_name'       => trim((string)($r['primary_provider'] ?? '')),
            'member_id'        => trim((string)($r['member_id'] ?: ($r['primary_policy_no'] ?? ''))),
            'policy_no'        => trim((string)($r['primary_policy_no'] ?? '')),
            'group_no'         => trim((string)($r['primary_group_no'] ?? '')),
            'plan_name'        => trim((string)($r['plan_name'] ?? '')),
            'effective_date'   => $r['effective_date'] ?? null,
            'subscriber_first' => $first,
            'subscriber_last'  => $last,
            'subscriber_dob'   => $r['subscriber_dob'] ?? null,
            'relationship'     => trim((string)($r['subscriber_relationship'] ?? '')),
            'employer'         => trim((string)($r['subscriber_employer'] ?? '')),
            'copay'            => $r['copay'] ?? null,
            'phone'            => $r['insurance_phone'] ?? null,
        ];
    }

    private function coverageFor(int $patientId, string $type): ?array {
        $r = Database::fetch("SELECT * FROM patient_insurance WHERE patient_id = ? AND insurance_type = ? ORDER BY id DESC LIMIT 1", [$patientId, $type]);
        if (!$r) return null;
        $cov = $this->coverageFromRow($r);
        $cov['_row_id'] = (int)$r['id'];
        if ($cov['payer_name'] === '' || stripos($cov['payer_name'], 'self pay') !== false || stripos($cov['payer_name'], 'uninsured') !== false) return null;
        return $cov;
    }

    /** Patient, provider, facility, coverage, payer for a claim - shared by readiness, 837 and CMS-1500. */
    public function context(array $c): array {
        $p = Database::fetch("SELECT * FROM patients WHERE id = ?", [$c['patient_id']]) ?: [];
        $patient = [
            'first'   => $this->dec($p['first_name_encrypted'] ?? ''),
            'last'    => $this->dec($p['last_name_encrypted'] ?? ''),
            'dob'     => $this->dec($p['dob_encrypted'] ?? ''),
            'phone'   => $this->dec($p['phone_encrypted'] ?? ''),
            'address' => $this->dec($p['address_encrypted'] ?? ''),
            'city'    => $p['city'] ?? '',
            'state'   => $p['state'] ?? '',
            'zip'     => $p['zip'] ?? '',
            'gender'  => $p['gender'] ?? '',
            'facility_id' => $p['facility_id'] ?? null,
        ];
        $providerId = null;
        if (!empty($c['encounter_id'])) {
            $n = Database::fetch("SELECT provider_id FROM clinical_notes WHERE id = ?", [$c['encounter_id']]);
            $providerId = $n['provider_id'] ?? null;
        }
        if (!$providerId) {
            $providerId = (Database::fetch("SELECT created_by FROM invoices WHERE id = ?", [$c['invoice_id']])['created_by'] ?? null);
        }
        $provider = Database::fetch("SELECT id, first_name, last_name, npi, taxonomy_code, facility_id FROM users WHERE id = ?", [$providerId]) ?: [];
        $facility = null;
        foreach ([$provider['facility_id'] ?? null, $patient['facility_id']] as $fid) {
            if ($fid && ($facility = Database::fetch("SELECT * FROM facilities WHERE id = ?", [$fid]))) break;
        }
        if (!$facility) {
            $facility = Database::fetch("SELECT * FROM facilities WHERE is_active = 1 AND npi IS NOT NULL AND npi <> '' ORDER BY id LIMIT 1")
                ?: Database::fetch("SELECT * FROM facilities WHERE is_active = 1 ORDER BY id LIMIT 1") ?: [];
        }
        $facility += ['facility_name' => '', 'legal_entity_name' => '', 'npi' => '', 'tax_id_ein' => '', 'address' => '', 'address_line1' => '', 'city' => '', 'state' => '', 'zip_code' => '', 'postal_code' => ''];
        $provider += ['first_name' => '', 'last_name' => '', 'npi' => '', 'taxonomy_code' => ''];
        $payer = Database::fetch("SELECT * FROM insurance_payers WHERE name = ?", [$c['payer_name']]) ?: [];
        return [
            'patient'  => $patient,
            'provider' => $provider,
            'facility' => $facility,
            'coverage' => json_decode($c['coverage_json'] ?? '', true) ?: [],
            'payer'    => $payer,
            'settings' => $this->settings(),
        ];
    }

    /** Checklist that gates Draft/Rejected -> Ready ('ready' items) and the 837 export ('edi' items). */
    public function readiness(array $c, array $lines, array $ctx): array {
        $pt = $ctx['patient']; $cov = $ctx['coverage']; $prov = $ctx['provider']; $fac = $ctx['facility']; $set = $ctx['settings'];
        $items = [];
        $add = function (string $key, string $label, bool $ok, string $detail = '', string $for = 'ready') use (&$items) {
            $items[] = ['key' => $key, 'label' => $label, 'ok' => $ok, 'detail' => $ok ? '' : $detail, 'for' => $for];
        };
        $rel = strtolower(trim((string)($cov['relationship'] ?? '')));
        $self = ($rel === '' || $rel === 'self' || $rel === 'patient');

        $add('payer', 'Payer selected', trim((string)$c['payer_name']) !== '', 'Choose the payer on the claim.');
        $add('payer_id', 'Payer ID code entered', trim((string)$c['payer_id_code']) !== '', 'Add the payer ID under Manage Payers (or edit the claim).');
        $add('member', 'Member / policy ID', trim((string)($cov['member_id'] ?? '')) !== '', 'Add the member ID to the patient\'s insurance.');
        $add('subscriber', 'Subscriber details', $self || (trim((string)($cov['subscriber_last'] ?? '')) !== '' && !empty($cov['subscriber_dob'])), 'Subscriber name and date of birth are needed when the patient is not the subscriber.');
        $add('patient_demo', 'Patient DOB, sex and address', $pt['dob'] !== '' && trim((string)$pt['gender']) !== '' && $pt['address'] !== '' && $pt['city'] !== '' && $pt['state'] !== '' && $pt['zip'] !== '', 'Complete the patient\'s date of birth, sex and full address.');
        $add('provider_npi', 'Rendering provider NPI', strlen(preg_replace('/\D/', '', (string)$prov['npi'])) === 10, 'Enter a 10-digit NPI on the provider\'s profile.');
        $add('provider_tax', 'Rendering provider taxonomy code', trim((string)$prov['taxonomy_code']) !== '', 'Enter the provider\'s taxonomy code on their profile.');
        $add('facility', 'Facility NPI, tax ID and address', strlen(preg_replace('/\D/', '', (string)$fac['npi'])) === 10 && trim((string)$fac['tax_id_ein']) !== '' && trim((string)($fac['address_line1'] ?: $fac['address'])) !== '' && $fac['city'] !== '' && $fac['state'] !== '' && trim((string)($fac['zip_code'] ?: $fac['postal_code'])) !== '', 'Complete the facility record (10-digit NPI, tax ID, address).');
        $badLines = 0;
        foreach ($lines as $l) {
            if (trim((string)$l['cpt_code']) === '' || $l['cpt_code'] === '-' || trim((string)$l['icd10_code']) === '') $badLines++;
        }
        $add('lines', 'Every line has a CPT and ICD-10 code', count($lines) > 0 && $badLines === 0, $badLines ? "{$badLines} line(s) missing a CPT or ICD-10 code." : 'The claim has no service lines.');
        $add('amount', 'Billed amount is greater than zero', (float)$c['billed_amount'] > 0, 'The claim total must be greater than zero.');
        $add('submitter', 'Submitter / receiver IDs (837 file)', trim((string)$set['billing_submitter_id']) !== '' && trim((string)$set['billing_receiver_id']) !== '' && trim((string)$set['billing_contact_name']) !== '' && trim((string)$set['billing_contact_phone']) !== '', 'Enter the submitter ID, receiver ID and contact under Manage Payers > 837 settings.', 'edi');

        $readyOk = true; $ediOk = true;
        foreach ($items as $i) {
            if (!$i['ok']) { $ediOk = false; if ($i['for'] === 'ready') $readyOk = false; }
        }
        return ['items' => $items, 'ready_ok' => $readyOk, 'edi_ok' => $ediOk];
    }

    public function firstFailure(array $readiness, string $for): string {
        foreach ($readiness['items'] as $i) {
            if (!$i['ok'] && ($for === 'edi' || $i['for'] === 'ready')) return $i['label'] . ' - ' . $i['detail'];
        }
        return '';
    }

    private function actionsFor(array $c, bool $canEdit, bool $canBillSecondary): array {
        $a = ['cms1500'];
        if (!$canEdit) return $a;
        switch ($c['status']) {
            case 'Draft':     $a = ['edit', 'ready', 'close', 'cms1500']; break;
            case 'Ready':     $a = ['draft', 'submit', 'edi837', 'cms1500', 'close']; break;
            case 'Submitted': $a = ['accept', 'reject', 'remit', 'edi837', 'cms1500', 'close']; break;
            case 'Accepted':  $a = ['remit', 'edi837', 'cms1500', 'close']; break;
            case 'Rejected':  $a = ['edit', 'ready', 'close', 'cms1500']; break;
            case 'Denied':    $a = ['appeal', 'reverse', 'close', 'cms1500']; break;
            case 'Appealed':  $a = ['remit', 'close', 'cms1500']; break;
            case 'Paid':
            case 'Partially Paid':
                $a = ['reverse', 'cms1500'];
                if ($canBillSecondary) array_unshift($a, 'bill_secondary');
                break;
            default: $a = ['cms1500'];
        }
        return $a;
    }

    private function canBillSecondary(array $c): bool {
        if ($c['sequence'] !== 'Primary' || !in_array($c['status'], ['Paid', 'Partially Paid'], true)) return false;
        if ((float)$c['patient_resp'] <= self::TOL) return false;
        if (Database::fetch("SELECT id FROM insurance_claims WHERE parent_claim_id = ? AND status <> 'Closed' LIMIT 1", [$c['id']])) return false;
        return $this->coverageFor((int)$c['patient_id'], 'Secondary') !== null;
    }

    private function detail(int $id): ?array {
        $c = $this->load($id);
        if (!$c) return null;
        $lines = $this->claimLines($id);
        $ctx = $this->context($c);
        $readiness = $this->readiness($c, $lines, $ctx);
        $canEdit = in_array($_SESSION['user_role'] ?? '', self::EDIT_ROLES, true);
        $payments = Database::fetchAll(
            "SELECT id, source, method, reference_no, amount, paid_at, voided_at FROM payments WHERE claim_id = ? ORDER BY id", [$id]
        );
        $adjustments = Database::fetchAll(
            "SELECT id, type, amount, reason, voided_at FROM adjustments WHERE claim_id = ? ORDER BY id", [$id]
        );
        $history = Database::fetchAll(
            "SELECT h.from_status, h.to_status, h.note, h.created_at, u.first_name, u.last_name
             FROM claim_status_history h LEFT JOIN users u ON u.id = h.user_id WHERE h.claim_id = ? ORDER BY h.id", [$id]
        );
        foreach ($history as &$h) {
            $h['user'] = trim(($h['first_name'] ?? '') . ' ' . ($h['last_name'] ?? ''));
            unset($h['first_name'], $h['last_name']);
        }
        unset($h);
        $canSec = $this->canBillSecondary($c);
        $dx = [];
        foreach ($lines as $l) {
            $code = trim((string)$l['icd10_code']);
            if ($code !== '' && !in_array($code, $dx, true)) $dx[] = $code;
        }
        return [
            'claim'         => $c + ['patient_name' => $this->patientName((int)$c['patient_id'])],
            'coverage'      => $ctx['coverage'],
            'provider'      => ['name' => trim($ctx['provider']['first_name'] . ' ' . $ctx['provider']['last_name']), 'npi' => $ctx['provider']['npi'], 'taxonomy_code' => $ctx['provider']['taxonomy_code']],
            'facility'      => ['name' => $ctx['facility']['facility_name'], 'npi' => $ctx['facility']['npi'], 'tax_id_ein' => $ctx['facility']['tax_id_ein']],
            'lines'         => $lines,
            'diagnoses'     => $dx,
            'payments'      => $payments,
            'adjustments'   => $adjustments,
            'history'       => $history,
            'readiness'     => $readiness,
            'edi'           => EdiStore::summary($c),
            'actions'       => $this->actionsFor($c, $canEdit, $canSec),
        ];
    }

    private function balanceOf(int $invoiceId): float {
        $r = BillingController::recalcInvoice($invoiceId);
        return (float)($r['balance'] ?? 0);
    }

    private function nextClaimNumber(int $id): string {
        return 'CLM-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
    }

    // ------------------------------------------------------------------ list / detail
    // GET /api/billing/claims?status=&payer=&q=&date_from=&date_to=
    public function index(): void {
        $this->checkAccess(self::VIEW_ROLES);
        $fid = $_SESSION['facility_id'] ?? null;
        $where = ['p.facility_id = ?']; $params = [$fid];
        $status = $_GET['status'] ?? '';
        if ($status === 'open') {
            $where[] = "c.status IN ('" . implode("','", self::OPEN_STATUSES) . "')";
        } elseif ($status !== '') {
            $where[] = 'c.status = ?'; $params[] = $status;
        }
        if (!empty($_GET['payer'])) { $where[] = 'c.payer_name = ?'; $params[] = $_GET['payer']; }
        if (!empty($_GET['date_from']) && $this->validDate($_GET['date_from'])) { $where[] = 'c.date_of_service >= ?'; $params[] = $_GET['date_from']; }
        if (!empty($_GET['date_to']) && $this->validDate($_GET['date_to'])) { $where[] = 'c.date_of_service <= ?'; $params[] = $_GET['date_to']; }

        $rows = Database::fetchAll(
            "SELECT c.id, c.claim_number, c.invoice_id, c.patient_id, c.sequence, c.status, c.payer_name, c.date_of_service,
                    c.billed_amount, c.insurance_paid, c.adjustment_amount, c.patient_resp, c.timely_filing_due,
                    i.invoice_number, DATEDIFF(CURDATE(), c.date_of_service) AS age_days
             FROM insurance_claims c JOIN invoices i ON i.id = c.invoice_id JOIN patients p ON p.id = i.patient_id
             WHERE " . implode(' AND ', $where) . " ORDER BY c.id DESC",
            $params
        );
        $q = strtolower(trim((string)($_GET['q'] ?? '')));
        $out = [];
        foreach ($rows as $r) {
            $r['patient_name'] = $this->patientName((int)$r['patient_id']);
            if ($q !== '' && strpos(strtolower($r['patient_name'] . ' ' . $r['claim_number'] . ' ' . $r['invoice_number']), $q) === false) continue;
            $out[] = $r;
        }
        $counts = [];
        foreach (Database::fetchAll(
            "SELECT c.status, COUNT(*) c FROM insurance_claims c JOIN invoices i ON i.id = c.invoice_id JOIN patients p ON p.id = i.patient_id WHERE p.facility_id = ? GROUP BY c.status",
            [$fid]
        ) as $r) $counts[$r['status']] = (int)$r['c'];

        $openList = "'" . implode("','", self::OPEN_STATUSES) . "'";
        $kpi = Database::fetch(
            "SELECT
               COALESCE(SUM(CASE WHEN c.status IN ($openList) THEN c.billed_amount - c.insurance_paid - c.adjustment_amount END), 0) AS outstanding,
               COALESCE(SUM(CASE WHEN c.status IN ('Submitted','Accepted','Appealed') THEN c.billed_amount END), 0) AS awaiting_amount,
               SUM(c.status IN ('Submitted','Accepted','Appealed')) AS awaiting_count,
               COALESCE(SUM(CASE WHEN c.status = 'Denied' THEN c.billed_amount END), 0) AS denied_amount,
               SUM(c.status = 'Denied') AS denied_count
             FROM insurance_claims c JOIN invoices i ON i.id = c.invoice_id JOIN patients p ON p.id = i.patient_id WHERE p.facility_id = ?",
            [$fid]
        );
        $patientBal = Database::fetch(
            "SELECT COALESCE(SUM(i.total_amount - i.paid_amount - COALESCE((SELECT SUM(a.amount) FROM adjustments a WHERE a.invoice_id = i.id AND a.voided_at IS NULL), 0)), 0) AS bal,
                    COUNT(*) AS n
             FROM invoices i JOIN patients p ON p.id = i.patient_id WHERE i.status = 'Patient Balance' AND p.facility_id = ?",
            [$fid]
        );
        $this->respond([
            'status' => 'success',
            'data'   => $out,
            'counts' => $counts,
            'kpis'   => [
                'outstanding'      => round((float)$kpi['outstanding'], 2),
                'awaiting_amount'  => round((float)$kpi['awaiting_amount'], 2),
                'awaiting_count'   => (int)$kpi['awaiting_count'],
                'denied_amount'    => round((float)$kpi['denied_amount'], 2),
                'denied_count'     => (int)$kpi['denied_count'],
                'patient_balance'  => round((float)$patientBal['bal'], 2),
                'patient_balance_count' => (int)$patientBal['n'],
            ],
        ]);
    }

    // GET /api/billing/ar-aging
    // Insurance AR = open claims (billed - paid - adjusted), aged from date of service.
    // Patient AR  = invoices with a balance that are not waiting on insurance, aged from invoice date.
    public function arAging(): void {
        $this->checkAccess(self::VIEW_ROLES);
        $buckets = ['b0_30' => [0, 30], 'b31_60' => [31, 60], 'b61_90' => [61, 90], 'b90_plus' => [91, PHP_INT_MAX]];
        $empty = fn() => ['amount' => 0.0, 'count' => 0];
        $out = ['insurance' => array_map($empty, $buckets) + ['total' => $empty()], 'patient' => array_map($empty, $buckets) + ['total' => $empty()]];
        $put = function (string $row, int $age, float $amount) use (&$out, $buckets) {
            foreach ($buckets as $key => [$lo, $hi]) {
                if ($age >= $lo && $age <= $hi) {
                    $out[$row][$key]['amount'] += $amount; $out[$row][$key]['count']++;
                    break;
                }
            }
            $out[$row]['total']['amount'] += $amount; $out[$row]['total']['count']++;
        };
        $fid = $_SESSION['facility_id'] ?? null;
        $open = "'" . implode("','", self::OPEN_STATUSES) . "'";
        foreach (Database::fetchAll(
            "SELECT c.billed_amount - c.insurance_paid - c.adjustment_amount AS due, DATEDIFF(CURDATE(), c.date_of_service) AS age
             FROM insurance_claims c JOIN invoices i ON i.id = c.invoice_id JOIN patients p ON p.id = i.patient_id
             WHERE c.status IN ($open) AND p.facility_id = ?",
            [$fid]
        ) as $r) {
            if ((float)$r['due'] > self::TOL) $put('insurance', max(0, (int)$r['age']), (float)$r['due']);
        }
        $sql = "SELECT i.total_amount - i.paid_amount - COALESCE((SELECT SUM(a.amount) FROM adjustments a WHERE a.invoice_id = i.id AND a.voided_at IS NULL), 0) AS due,
                       DATEDIFF(CURDATE(), i.invoice_date) AS age
                FROM invoices i JOIN patients p ON p.id = i.patient_id WHERE i.status IN ('Issued','Partially Paid','Patient Balance','Overdue') AND p.facility_id = ?";
        foreach (Database::fetchAll($sql, [$fid]) as $r) {
            if ((float)$r['due'] > self::TOL) $put('patient', max(0, (int)$r['age']), (float)$r['due']);
        }
        foreach (['insurance', 'patient'] as $row) {
            foreach ($out[$row] as $k => $v) $out[$row][$k]['amount'] = round($v['amount'], 2);
        }
        $this->respond(['status' => 'success', 'as_of' => date('Y-m-d')] + $out);
    }

    // GET /api/billing/claims/{id}
    public function show(array $params): void {
        $this->checkAccess(self::VIEW_ROLES);
        $d = $this->detail((int)($params['id'] ?? 0));
        if (!$d) { $this->fail('Claim not found.', 404); return; }
        $this->respond(['status' => 'success'] + $d);
    }

    // ------------------------------------------------------------------ create / edit
    // POST /api/billing/claims  { invoice_id }  -> Primary claim
    public function store(): void {
        $this->checkAccess(self::EDIT_ROLES);
        $in = $this->input();
        $invoiceId = (int)($in['invoice_id'] ?? 0);
        $inv = $invoiceId ? Database::fetch(
            "SELECT i.* FROM invoices i JOIN patients p ON p.id = i.patient_id WHERE i.id = ? AND p.facility_id = ?",
            [$invoiceId, $_SESSION['facility_id'] ?? null]
        ) : null;
        if (!$inv) { $this->fail('Invoice not found.', 404); return; }
        if ($inv['status'] === 'Draft') { $this->fail('Issue the invoice before creating a claim.'); return; }
        if ((float)$inv['discount'] > 0) { $this->fail('Invoices with a discount cannot be billed to insurance. Remove the discount or bill the patient.'); return; }
        if (Database::fetch("SELECT id FROM insurance_claims WHERE invoice_id = ? AND sequence = 'Primary' AND status <> 'Closed' LIMIT 1", [$invoiceId])) {
            $this->fail('This invoice already has an active primary claim.'); return;
        }
        $cov = $this->coverageFor((int)$inv['patient_id'], 'Primary');
        if (!$cov) { $this->fail('The patient has no primary insurance on file. Add it in the patient record first.'); return; }
        $items = Database::fetchAll("SELECT * FROM invoice_line_items WHERE invoice_id = ? ORDER BY id", [$invoiceId]);
        if (!$items) { $this->fail('The invoice has no line items.'); return; }

        $payer = Database::fetch("SELECT * FROM insurance_payers WHERE name = ?", [$cov['payer_name']]);
        $payerIdCode = trim((string)($payer['payer_id_code'] ?? ''));
        if ($payerIdCode === '') {
            $row = Database::fetch("SELECT payer_id FROM patient_insurance WHERE id = ?", [$cov['_row_id']]);
            $payerIdCode = trim((string)($row['payer_id'] ?? ''));
        }
        $dos = date('Y-m-d', strtotime($inv['invoice_date']));
        if (!empty($inv['encounter_id'])) {
            $n = Database::fetch("SELECT note_date FROM clinical_notes WHERE id = ?", [$inv['encounter_id']]);
            if (!empty($n['note_date'])) $dos = date('Y-m-d', strtotime($n['note_date']));
        }
        $billed = 0.0;
        foreach ($items as $it) $billed += (float)$it['total_price'];
        $billed = round($billed, 2);
        unset($cov['_row_id']);

        Database::beginTransaction();
        try {
            Database::query(
                "INSERT INTO insurance_claims (invoice_id, patient_id, encounter_id, patient_insurance_id, sequence, status, payer_name, payer_id_code, coverage_json, date_of_service, billed_amount, timely_filing_due, created_by)
                 VALUES (?, ?, ?, ?, 'Primary', 'Draft', ?, ?, ?, ?, ?, ?, ?)",
                [$invoiceId, $inv['patient_id'], $inv['encounter_id'] ?: null, null, $cov['payer_name'], $payerIdCode ?: null, json_encode($cov), $dos, $billed, date('Y-m-d', strtotime($dos . ' +365 days')), $_SESSION['user_id']]
            );
            $claimId = (int)Database::lastInsertId();
            Database::query("UPDATE insurance_claims SET claim_number = ? WHERE id = ?", [$this->nextClaimNumber($claimId), $claimId]);

            $dx = [];
            foreach ($items as $it) {
                $code = preg_split('/\s+/', trim((string)$it['icd10_code']))[0] ?? '';
                if ($code !== '' && !in_array($code, $dx, true) && count($dx) < 12) $dx[] = $code;
            }
            foreach ($items as $it) {
                $code = preg_split('/\s+/', trim((string)$it['icd10_code']))[0] ?? '';
                $ptr = ($code !== '' && ($pos = array_search($code, $dx, true)) !== false) ? chr(65 + $pos) : null;
                $cpt = trim((string)$it['cpt_code']);
                Database::query(
                    "INSERT INTO claim_lines (claim_id, invoice_line_item_id, cpt_code, cpt_description, icd10_code, dx_pointer, units, charge)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$claimId, $it['id'], $cpt === '-' ? '' : $cpt, $it['cpt_description'], $code ?: null, $ptr, max(1, (int)$it['quantity']), $this->money($it['total_price'])]
                );
            }
            $this->history($claimId, null, 'Draft', 'Claim created from ' . $inv['invoice_number']);
            Database::query("UPDATE invoices SET billing_type = 'Insurance' WHERE id = ?", [$invoiceId]);
            BillingController::recalcInvoice($invoiceId);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not create the claim.', 500);
            return;
        }
        $this->audit((int)$inv['patient_id'], 'Create Claim ' . $this->nextClaimNumber($claimId) . ' for Invoice ' . $inv['invoice_number'], $claimId);
        $this->respond(['status' => 'success', 'message' => 'Claim ' . $this->nextClaimNumber($claimId) . ' created.', 'claim_id' => $claimId]);
    }

    // PUT /api/billing/claims/{id}  (Draft / Rejected only)
    public function update(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if (!in_array($c['status'], ['Draft', 'Rejected'], true)) { $this->fail('Only Draft or Rejected claims can be edited.'); return; }
        $in = $this->input();

        $payerName = array_key_exists('payer_name', $in) ? trim((string)$in['payer_name']) : $c['payer_name'];
        $payerCode = array_key_exists('payer_id_code', $in) ? trim((string)$in['payer_id_code']) : (string)$c['payer_id_code'];
        $notes = array_key_exists('notes', $in) ? trim((string)$in['notes']) : (string)$c['notes'];
        if ($payerName === '') { $this->fail('Payer name is required.'); return; }
        if ($payerCode !== '' && !preg_match('/^[A-Za-z0-9\-]{1,30}$/', $payerCode)) { $this->fail('Payer ID may contain only letters, numbers and dashes.'); return; }

        $lineUpdates = [];
        foreach (($in['lines'] ?? []) as $l) {
            $lid = (int)($l['id'] ?? 0);
            $mods = strtoupper(trim((string)($l['modifiers'] ?? '')));
            $ptr = strtoupper(trim((string)($l['dx_pointer'] ?? '')));
            if ($mods !== '' && !preg_match('/^[A-Z0-9]{2}(,[A-Z0-9]{2}){0,3}$/', $mods)) { $this->fail('Modifiers must be 2-character codes separated by commas (max 4).'); return; }
            if ($ptr !== '' && !preg_match('/^[A-L]{1,4}$/', $ptr)) { $this->fail('Diagnosis pointer must be letters A-L (max 4).'); return; }
            $icd = array_key_exists('icd10_code', $l) ? strtoupper(str_replace(' ', '', trim((string)$l['icd10_code']))) : null;
            if ($icd !== null && $icd !== '' && !preg_match('/^[A-TV-Z][0-9][0-9AB](\.?[0-9A-TV-Z]{1,4})?$/', $icd)) { $this->fail("ICD-10 code \"{$icd}\" is not valid (example: I10 or I48.91)."); return; }
            $lineUpdates[] = [$lid, $mods, $ptr, $icd];
        }
        Database::beginTransaction();
        try {
            Database::query("UPDATE insurance_claims SET payer_name = ?, payer_id_code = ?, notes = ? WHERE id = ?", [$payerName, $payerCode ?: null, $notes ?: null, $id]);
            foreach ($lineUpdates as [$lid, $mods, $ptr, $icd]) {
                if ($icd === null) {
                    Database::query("UPDATE claim_lines SET modifiers = ?, dx_pointer = ? WHERE id = ? AND claim_id = ?", [$mods ?: null, $ptr ?: null, $lid, $id]);
                } else {
                    Database::query("UPDATE claim_lines SET modifiers = ?, dx_pointer = ?, icd10_code = ? WHERE id = ? AND claim_id = ?", [$mods ?: null, $ptr ?: null, $icd ?: null, $lid, $id]);
                }
            }
            // keep pointers coherent: a line with a diagnosis but no (or an out-of-range) pointer points at its own diagnosis
            $rows = $this->claimLines($id);
            $dxList = [];
            foreach ($rows as $r) { $c10 = trim((string)$r['icd10_code']); if ($c10 !== '' && !in_array($c10, $dxList, true) && count($dxList) < 12) $dxList[] = $c10; }
            foreach ($rows as $r) {
                $c10 = trim((string)$r['icd10_code']);
                if ($c10 === '') continue;
                $cur = preg_replace('/[^A-L]/', '', strtoupper((string)$r['dx_pointer']));
                $outOfRange = $cur !== '' && (ord($cur[strlen($cur) - 1]) - 64) > count($dxList);
                if ($cur === '' || $outOfRange) {
                    $pos = array_search($c10, $dxList, true);
                    if ($pos !== false) Database::query("UPDATE claim_lines SET dx_pointer = ? WHERE id = ? AND claim_id = ?", [chr(65 + $pos), $r['id'], $id]);
                }
            }
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not save the claim.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], "Edit Claim {$c['claim_number']}", $id);
        $this->respond(['status' => 'success', 'message' => 'Claim saved.']);
    }

    // ------------------------------------------------------------------ lifecycle
    // POST /api/billing/claims/{id}/status  { status, note?, submitted_at?, submission_ref?, payer_claim_no? }
    public function setStatus(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        $in = $this->input();
        $to = (string)($in['status'] ?? '');
        $note = trim((string)($in['note'] ?? ''));
        if (!in_array($to, self::TRANSITIONS[$c['status']] ?? [], true)) {
            $this->fail("A {$c['status']} claim cannot move to {$to}.");
            return;
        }
        $sets = ['status = ?']; $vals = [$to];
        if ($to === 'Ready') {
            $lines = $this->claimLines($id);
            $readiness = $this->readiness($c, $lines, $this->context($c));
            if (!$readiness['ready_ok']) {
                $this->fail('Claim is not ready: ' . $this->firstFailure($readiness, 'ready'), 400, ['readiness' => $readiness]);
                return;
            }
            $sets[] = 'denial_code = NULL'; $sets[] = 'denial_reason = NULL';
        } elseif ($to === 'Submitted') {
            $when = trim((string)($in['submitted_at'] ?? '')) ?: date('Y-m-d');
            if (!$this->validDate($when) || $when > date('Y-m-d')) { $this->fail('Submission date is not valid.'); return; }
            $sets[] = 'submitted_at = ?'; $vals[] = $when . ' 00:00:00';
            $ref = trim((string)($in['submission_ref'] ?? ''));
            $sets[] = 'submission_ref = ?'; $vals[] = $ref !== '' ? mb_substr($ref, 0, 100) : null;
        } elseif ($to === 'Accepted') {
            $pcn = trim((string)($in['payer_claim_no'] ?? ''));
            if ($pcn !== '') { $sets[] = 'payer_claim_no = ?'; $vals[] = mb_substr($pcn, 0, 100); }
        } elseif ($to === 'Rejected') {
            if ($note === '') { $this->fail('Enter the rejection reason.'); return; }
            $sets[] = 'denial_reason = ?'; $vals[] = mb_substr($note, 0, 255);
        }
        $vals[] = $id;
        Database::beginTransaction();
        try {
            Database::query("UPDATE insurance_claims SET " . implode(', ', $sets) . " WHERE id = ?", $vals);
            $this->history($id, $c['status'], $to, $note);
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not update the claim.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], "Claim {$c['claim_number']}: {$c['status']} -> {$to}", $id);
        $this->respond(['status' => 'success', 'message' => "Claim marked {$to}."]);
    }

    // POST /api/billing/claims/{id}/remit
    public function remit(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        $r = $this->applyRemit($c, $this->input());
        if (!$r['ok']) { $this->fail($r['message'], $r['code']); return; }
        $this->respond(['status' => 'success', 'message' => $r['message'], 'new_status' => $r['new_status']]);
    }

    /**
     * The single money path for a payer response (manual entry AND a posted 835).
     * $in: lines[{id, allowed, paid, denial_code}], payment_method (Check|ACH|Other), reference_no, paid_at,
     *      payer_claim_no, denial_reason, source_note (appended to the history entry).
     * Per line: denied (denial_code) -> nothing paid; otherwise allowed <= charge, paid <= allowed,
     * contractual adjustment = charge - allowed, patient responsibility = allowed - paid.
     * Returns ['ok' => bool, 'message' => string, 'code' => int, 'new_status' => string].
     */
    public function applyRemit(array $c, array $in): array {
        $id = (int)$c['id'];
        $err = fn(string $m, int $code = 400) => ['ok' => false, 'message' => $m, 'code' => $code];
        if (!in_array($c['status'], ['Submitted', 'Accepted', 'Appealed'], true)) return $err('Remittance can only be posted on a submitted, accepted or appealed claim.');
        $method = (string)($in['payment_method'] ?? 'ACH');
        $reference = trim((string)($in['reference_no'] ?? ''));
        $paidAt = trim((string)($in['paid_at'] ?? '')) ?: date('Y-m-d');
        $payerClaimNo = trim((string)($in['payer_claim_no'] ?? ''));
        if (!in_array($method, ['Check', 'ACH', 'Other'], true)) return $err('Choose how the insurer paid (Check, ACH or Other).');
        if (!$this->validDate($paidAt) || $paidAt > date('Y-m-d')) return $err('Payment date is not valid.');

        $lines = $this->claimLines($id);
        $byId = [];
        foreach (($in['lines'] ?? []) as $l) $byId[(int)($l['id'] ?? 0)] = $l;

        $calc = [];
        $totalPaid = 0.0; $totalAdj = 0.0; $totalAllowed = 0.0; $totalResp = 0.0; $deniedCount = 0; $firstDenial = ''; $denialReason = trim((string)($in['denial_reason'] ?? ''));
        foreach ($lines as $l) {
            $inp = $byId[(int)$l['id']] ?? null;
            if ($inp === null) return $err('Enter the payer response for every line.');
            $charge = $this->money($l['charge']);
            $denial = strtoupper(trim((string)($inp['denial_code'] ?? '')));
            if ($denial !== '') {
                if (!preg_match('/^[A-Z0-9\-]{1,20}$/', $denial)) return $err("Line {$l['cpt_code']}: denial code is not valid.");
                $calc[] = ['id' => $l['id'], 'allowed' => 0.0, 'paid' => 0.0, 'adj' => 0.0, 'resp' => 0.0, 'denial' => $denial];
                $deniedCount++;
                if ($firstDenial === '') $firstDenial = $denial;
                continue;
            }
            $allowed = $this->money($inp['allowed'] ?? 0);
            $paid = $this->money($inp['paid'] ?? 0);
            if ($allowed < 0 || $paid < 0) return $err("Line {$l['cpt_code']}: amounts cannot be negative.");
            if ($allowed > $charge + self::TOL) return $err("Line {$l['cpt_code']}: allowed amount cannot exceed the charge (\${$charge}).");
            if ($paid > $allowed + self::TOL) return $err("Line {$l['cpt_code']}: paid amount cannot exceed the allowed amount.");
            $adj = $this->money($charge - $allowed);
            $resp = $this->money($allowed - $paid);
            $calc[] = ['id' => $l['id'], 'allowed' => $allowed, 'paid' => $paid, 'adj' => $adj, 'resp' => $resp, 'denial' => null];
            $totalPaid += $paid; $totalAdj += $adj; $totalAllowed += $allowed; $totalResp += $resp;
        }
        $totalPaid = round($totalPaid, 2); $totalAdj = round($totalAdj, 2); $totalResp = round($totalResp, 2); $totalAllowed = round($totalAllowed, 2);
        if ($totalPaid > 0 && $reference === '') return $err('Enter the check or EFT/trace number for the payment.');
        if ($deniedCount === count($lines)) {
            $newStatus = 'Denied';
            if ($denialReason === '') return $err('Enter the denial reason.');
        } elseif ($deniedCount > 0) {
            $newStatus = 'Partially Paid';
        } else {
            $newStatus = 'Paid';
        }

        Database::beginTransaction();
        try {
            Database::fetch("SELECT id FROM invoices WHERE id = ? FOR UPDATE", [$c['invoice_id']]);
            $bal = $this->balanceOf((int)$c['invoice_id']);
            if ($totalPaid + $totalAdj > $bal + self::TOL) {
                Database::rollBack();
                return $err('This remittance ($' . number_format($totalPaid + $totalAdj, 2) . ' paid + adjusted) exceeds the invoice balance ($' . number_format($bal, 2) . ').');
            }
            foreach ($calc as $x) {
                Database::query(
                    "UPDATE claim_lines SET allowed = ?, paid = ?, adjustment = ?, patient_resp = ?, denial_code = ?, adjudicated = 1 WHERE id = ? AND claim_id = ?",
                    [$x['allowed'], $x['paid'], $x['adj'], $x['resp'], $x['denial'], $x['id'], $id]
                );
            }
            if ($totalPaid > 0) {
                Database::query(
                    "INSERT INTO payments (invoice_id, patient_id, claim_id, source, method, reference_no, amount, paid_at, notes, received_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$c['invoice_id'], $c['patient_id'], $id, $c['sequence'] === 'Primary' ? 'Primary Insurance' : 'Secondary Insurance', $method, $reference, $totalPaid, $paidAt, "Insurance remittance {$c['claim_number']}", $_SESSION['user_id']]
                );
            }
            if ($totalAdj > 0) {
                Database::query(
                    "INSERT INTO adjustments (invoice_id, claim_id, type, amount, reason, created_by) VALUES (?, ?, 'Contractual', ?, ?, ?)",
                    [$c['invoice_id'], $id, $totalAdj, "Contractual adjustment {$c['claim_number']}", $_SESSION['user_id']]
                );
            }
            Database::query(
                "UPDATE insurance_claims SET status = ?, allowed_amount = ?, insurance_paid = ?, adjustment_amount = ?, patient_resp = ?, payer_claim_no = COALESCE(NULLIF(?, ''), payer_claim_no), denial_code = ?, denial_reason = ? WHERE id = ?",
                [$newStatus, $totalAllowed, $totalPaid, $totalAdj, $totalResp, $payerClaimNo, $firstDenial ?: null, $newStatus === 'Denied' ? mb_substr($denialReason, 0, 255) : null, $id]
            );
            $note = $newStatus === 'Denied'
                ? "Denied ({$firstDenial}): {$denialReason}"
                : sprintf('Remit posted: paid $%s, contractual adj $%s, patient resp $%s%s', number_format($totalPaid, 2), number_format($totalAdj, 2), number_format($totalResp, 2), $deniedCount ? ", {$deniedCount} line(s) denied" : '');
            if (!empty($in['source_note'])) $note .= ' [' . $in['source_note'] . ']';
            $this->history($id, $c['status'], $newStatus, $note);
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            return $err('Could not post the remittance.', 500);
        }
        $this->audit((int)$c['patient_id'], "Post remittance on {$c['claim_number']}: paid $" . number_format($totalPaid, 2) . " ({$newStatus})", $id);
        return ['ok' => true, 'message' => "Remittance posted. Claim is now {$newStatus}.", 'code' => 200, 'new_status' => $newStatus];
    }

    // POST /api/billing/claims/{id}/reverse  { reason } - undo a posted remittance (voids its payment + adjustments)
    public function reverseRemit(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if (!in_array($c['status'], ['Paid', 'Partially Paid', 'Denied'], true)) { $this->fail('Only a claim with a posted remittance can be reversed.'); return; }
        $reason = trim((string)($this->input()['reason'] ?? ''));
        if ($reason === '') { $this->fail('A reason is required to reverse a remittance.'); return; }
        if (Database::fetch("SELECT id FROM insurance_claims WHERE parent_claim_id = ? AND status <> 'Closed' LIMIT 1", [$id])) {
            $this->fail('A secondary claim was created from this remittance. Close the secondary claim first.'); return;
        }
        Database::beginTransaction();
        try {
            Database::query("UPDATE payments SET voided_at = NOW(), voided_by = ?, void_reason = ? WHERE claim_id = ? AND voided_at IS NULL", [$_SESSION['user_id'], 'Remittance reversed: ' . mb_substr($reason, 0, 200), $id]);
            Database::query("UPDATE adjustments SET voided_at = NOW() WHERE claim_id = ? AND voided_at IS NULL", [$id]);
            Database::query("UPDATE claim_lines SET allowed = 0, paid = 0, adjustment = 0, patient_resp = 0, denial_code = NULL, adjudicated = 0 WHERE claim_id = ?", [$id]);
            Database::query("UPDATE insurance_claims SET status = 'Submitted', allowed_amount = 0, insurance_paid = 0, adjustment_amount = 0, patient_resp = 0, denial_code = NULL, denial_reason = NULL WHERE id = ?", [$id]);
            $this->history($id, $c['status'], 'Submitted', 'Remittance reversed: ' . $reason);
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not reverse the remittance.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], "Reverse remittance on {$c['claim_number']}: {$reason}", $id);
        $this->respond(['status' => 'success', 'message' => 'Remittance reversed. Claim is back to Submitted.']);
    }

    // POST /api/billing/claims/{id}/appeal  { note }
    public function appeal(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if ($c['status'] !== 'Denied') { $this->fail('Only a denied claim can be appealed.'); return; }
        $note = trim((string)($this->input()['note'] ?? ''));
        if ($note === '') { $this->fail('Enter the appeal details.'); return; }
        Database::beginTransaction();
        try {
            Database::query("UPDATE insurance_claims SET status = 'Appealed', appeal_note = ? WHERE id = ?", [$note, $id]);
            $this->history($id, 'Denied', 'Appealed', $note);
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not save the appeal.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], "Appeal claim {$c['claim_number']}", $id);
        $this->respond(['status' => 'success', 'message' => 'Appeal recorded.']);
    }

    // POST /api/billing/claims/{id}/close  { disposition: 'patient'|'write_off', reason }
    public function close(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if (!in_array($c['status'], self::OPEN_STATUSES, true)) { $this->fail('This claim is already settled.'); return; }
        $in = $this->input();
        $disp = (string)($in['disposition'] ?? '');
        $reason = trim((string)($in['reason'] ?? ''));
        if (!in_array($disp, ['patient', 'write_off'], true)) { $this->fail('Choose whether to move the balance to the patient or write it off.'); return; }
        if ($reason === '') { $this->fail('A reason is required to close a claim.'); return; }

        Database::beginTransaction();
        try {
            Database::fetch("SELECT id FROM invoices WHERE id = ? FOR UPDATE", [$c['invoice_id']]);
            $note = 'Closed - balance moved to patient: ' . $reason;
            if ($disp === 'write_off') {
                $bal = $this->balanceOf((int)$c['invoice_id']);
                $amount = round(min((float)$c['billed_amount'], $bal), 2);
                if ($amount > 0) {
                    Database::query(
                        "INSERT INTO adjustments (invoice_id, claim_id, type, amount, reason, created_by) VALUES (?, ?, 'Write-off', ?, ?, ?)",
                        [$c['invoice_id'], $id, $amount, mb_substr("Claim {$c['claim_number']} closed: {$reason}", 0, 255), $_SESSION['user_id']]
                    );
                }
                $note = 'Closed - $' . number_format($amount, 2) . ' written off: ' . $reason;
            }
            Database::query("UPDATE insurance_claims SET status = 'Closed' WHERE id = ?", [$id]);
            $this->history($id, $c['status'], 'Closed', $note);
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not close the claim.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], "Close claim {$c['claim_number']} ({$disp}): {$reason}", $id);
        $this->respond(['status' => 'success', 'message' => $disp === 'write_off' ? 'Claim closed and written off.' : 'Claim closed. The balance is now the patient\'s.']);
    }

    // POST /api/billing/claims/{id}/bill-secondary
    public function billSecondary(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if ($c['sequence'] !== 'Primary' || !in_array($c['status'], ['Paid', 'Partially Paid'], true)) { $this->fail('Secondary billing starts from a paid primary claim.'); return; }
        if ((float)$c['patient_resp'] <= self::TOL) { $this->fail('There is no patient responsibility left to bill to a secondary payer.'); return; }
        if (Database::fetch("SELECT id FROM insurance_claims WHERE parent_claim_id = ? AND status <> 'Closed' LIMIT 1", [$id])) { $this->fail('A secondary claim already exists for this claim.'); return; }
        $cov = $this->coverageFor((int)$c['patient_id'], 'Secondary');
        if (!$cov) { $this->fail('The patient has no secondary insurance on file.'); return; }
        $payer = Database::fetch("SELECT * FROM insurance_payers WHERE name = ?", [$cov['payer_name']]);
        $payerIdCode = trim((string)($payer['payer_id_code'] ?? ''));
        unset($cov['_row_id']);
        $lines = array_values(array_filter($this->claimLines($id), fn($l) => (float)$l['patient_resp'] > self::TOL));
        if (!$lines) { $this->fail('No line has a remaining patient responsibility.'); return; }
        $billed = 0.0;
        foreach ($lines as $l) $billed += (float)$l['patient_resp'];
        $billed = round($billed, 2);

        Database::beginTransaction();
        try {
            Database::query(
                "INSERT INTO insurance_claims (invoice_id, patient_id, encounter_id, parent_claim_id, sequence, status, payer_name, payer_id_code, coverage_json, date_of_service, billed_amount, timely_filing_due, created_by)
                 VALUES (?, ?, ?, ?, 'Secondary', 'Draft', ?, ?, ?, ?, ?, ?, ?)",
                [$c['invoice_id'], $c['patient_id'], $c['encounter_id'], $id, $cov['payer_name'], $payerIdCode ?: null, json_encode($cov), $c['date_of_service'], $billed, date('Y-m-d', strtotime($c['date_of_service'] . ' +365 days')), $_SESSION['user_id']]
            );
            $newId = (int)Database::lastInsertId();
            Database::query("UPDATE insurance_claims SET claim_number = ? WHERE id = ?", [$this->nextClaimNumber($newId), $newId]);
            foreach ($lines as $l) {
                Database::query(
                    "INSERT INTO claim_lines (claim_id, invoice_line_item_id, cpt_code, cpt_description, modifiers, icd10_code, dx_pointer, units, charge)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$newId, $l['invoice_line_item_id'], $l['cpt_code'], $l['cpt_description'], $l['modifiers'], $l['icd10_code'], $l['dx_pointer'], $l['units'], $this->money($l['patient_resp'])]
                );
            }
            $this->history($newId, null, 'Draft', "Secondary claim created after primary {$c['claim_number']}");
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('Could not create the secondary claim.', 500);
            return;
        }
        $this->audit((int)$c['patient_id'], 'Create secondary claim ' . $this->nextClaimNumber($newId) . " from {$c['claim_number']}", $newId);
        $this->respond(['status' => 'success', 'message' => 'Secondary claim ' . $this->nextClaimNumber($newId) . ' created.', 'claim_id' => $newId]);
    }

    // ------------------------------------------------------------------ exports
    public function exportContext(array $c): array {
        $ctx = $this->context($c);
        $ctx['claim'] = $c;
        $ctx['lines'] = $this->claimLines((int)$c['id']);
        $ctx['claim_total'] = $c['billed_amount'];
        $ctx['parent'] = null;
        if ($c['sequence'] === 'Secondary' && !empty($c['parent_claim_id'])) {
            $pc = $this->load((int)$c['parent_claim_id']);
            if ($pc) {
                $ctx['parent'] = [
                    'claim'    => $pc,
                    'lines'    => $this->claimLines((int)$pc['id']),
                    'coverage' => json_decode($pc['coverage_json'] ?? '', true) ?: [],
                    'payer'    => Database::fetch("SELECT * FROM insurance_payers WHERE name = ?", [$pc['payer_name']]) ?: [],
                ];
                // SV1 must carry the original charge, so the total is the sum of the original line charges
                $tot = 0.0;
                foreach ($ctx['lines'] as $l) {
                    foreach ($ctx['parent']['lines'] as $pl) {
                        if ((int)$pl['invoice_line_item_id'] === (int)$l['invoice_line_item_id']) { $tot += (float)$pl['charge']; break; }
                    }
                }
                $ctx['claim_total'] = round($tot, 2);
            }
        }
        return $ctx;
    }

    // GET /api/billing/claims/{id}/837
    public function edi837(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        if ($c['status'] === 'Closed') { $this->fail('A closed claim cannot be exported.'); return; }
        $ctx = $this->exportContext($c);
        $readiness = $this->readiness($c, $ctx['lines'], $ctx);
        if (!$readiness['edi_ok']) {
            $this->fail('Cannot create the 837 file: ' . $this->firstFailure($readiness, 'edi'), 400, ['readiness' => $readiness]);
            return;
        }
        $file = Claim837Generator::build($ctx);
        $this->audit((int)$c['patient_id'], "Export 837P for claim {$c['claim_number']}", $id);   // PHI is never logged, only the fact of the export
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $c['claim_number'] . '.837"');
        header('Cache-Control: no-store');
        echo $file;
    }

    // GET /api/billing/claims/{id}/cms1500  - print-friendly data sheet laid out by CMS-1500 box (not the red-ink form)
    public function cms1500(array $params): void {
        $this->checkAccess(self::VIEW_ROLES);
        $id = (int)($params['id'] ?? 0);
        $c = $this->load($id);
        if (!$c) { $this->fail('Claim not found.', 404); return; }
        $ctx = $this->exportContext($c);
        $pt = $ctx['patient']; $cov = $ctx['coverage']; $prov = $ctx['provider']; $fac = $ctx['facility'];
        $e = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $usd = fn($v) => number_format((float)$v, 2);
        $rel = strtolower(trim((string)($cov['relationship'] ?? '')));
        $self = ($rel === '' || $rel === 'self' || $rel === 'patient');
        $dobFmt = fn($d) => $d ? date('m / d / Y', strtotime($d)) : '';
        $dx = [];
        foreach ($ctx['lines'] as $l) {
            $code = trim((string)$l['icd10_code']);
            if ($code !== '' && !in_array($code, $dx, true) && count($dx) < 12) $dx[] = $code;
        }
        $hasOther = false; $otherName = ''; $otherPolicy = ''; $otherPlan = '';
        if ($c['sequence'] === 'Secondary' && $ctx['parent']) {
            $hasOther = true;
            $pc = $ctx['parent']['coverage'];
            $pRel = strtolower(trim((string)($pc['relationship'] ?? '')));
            $pSelf = ($pRel === '' || $pRel === 'self' || $pRel === 'patient');
            $otherName = $pSelf
                ? $pt['last'] . ', ' . $pt['first']
                : trim(($pc['subscriber_last'] ?? '') . ', ' . ($pc['subscriber_first'] ?? ''));
            $otherPolicy = $pc['group_no'] ?? '';
            $otherPlan = $pc['plan_name'] ?? ($ctx['parent']['claim']['payer_name'] ?? '');
        } elseif ($c['sequence'] === 'Primary' && $this->coverageFor((int)$c['patient_id'], 'Secondary')) {
            $hasOther = true;
        }
        $paidByOthers = 0.0;
        if ($c['sequence'] === 'Secondary' && $ctx['parent']) $paidByOthers = (float)$ctx['parent']['claim']['insurance_paid'];
        $lineRows = '';
        $n = 0;
        foreach ($ctx['lines'] as $l) {
            $n++;
            $ptrs = preg_replace('/[^A-L]/', '', strtoupper((string)$l['dx_pointer']));
            $charge = (float)$l['charge'];
            if ($c['sequence'] === 'Secondary' && $ctx['parent']) {
                foreach ($ctx['parent']['lines'] as $pl) {
                    if ((int)$pl['invoice_line_item_id'] === (int)$l['invoice_line_item_id']) { $charge = (float)$pl['charge']; break; }
                }
            }
            $lineRows .= '<tr><td>' . $n . '</td><td>' . $e(date('m/d/y', strtotime($c['date_of_service']))) . '</td><td>11</td><td>' . $e($l['cpt_code']) . '</td><td>' . $e(str_replace(',', ' ', (string)$l['modifiers'])) . '</td><td>' . $e($ptrs) . '</td><td class="r">' . $usd($charge) . '</td><td class="r">' . (int)$l['units'] . '</td><td>' . $e($prov['npi']) . '</td></tr>';
        }
        $this->audit((int)$c['patient_id'], "View CMS-1500 sheet for claim {$c['claim_number']}", $id);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        $box = function (string $label, string $value, string $cls = '') use ($e) {
            return '<div class="box ' . $cls . '"><div class="lb">' . $label . '</div><div class="vl">' . ($value === '' ? '&nbsp;' : $e($value)) . '</div></div>';
        };
        $totalCharge = $c['sequence'] === 'Secondary' && $ctx['parent'] ? $ctx['claim_total'] : $c['billed_amount'];
        echo '<!doctype html><html><head><meta charset="utf-8"><title>' . $e($c['claim_number']) . ' - CMS-1500 data sheet</title><style>
            body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#111;margin:20px;}
            h1{font-size:16px;margin:0 0 4px;} .sub{color:#555;margin-bottom:12px;}
            .grid{display:grid;grid-template-columns:repeat(6,1fr);gap:0;border:1px solid #333;}
            .box{border:1px solid #999;padding:4px 6px;min-height:34px;} .box.w2{grid-column:span 2;} .box.w3{grid-column:span 3;} .box.w6{grid-column:span 6;}
            .lb{font-size:9px;color:#555;text-transform:uppercase;} .vl{font-size:12px;font-weight:600;margin-top:2px;}
            table{width:100%;border-collapse:collapse;margin-top:10px;} th,td{border:1px solid #999;padding:4px 6px;text-align:left;font-size:11px;} th{background:#eee;font-size:9px;text-transform:uppercase;} .r{text-align:right;}
            .note{margin-top:12px;font-size:10px;color:#666;} .noprint{margin-bottom:12px;} @media print{.noprint{display:none;}}
        </style></head><body>
        <div class="noprint"><button onclick="window.print()">Print</button></div>
        <h1>Health Insurance Claim - CMS-1500 data sheet</h1>
        <div class="sub">Claim ' . $e($c['claim_number']) . ' &middot; ' . $e($c['sequence']) . ' &middot; Status: ' . $e($c['status']) . '</div>
        <div class="grid">'
            . $box('1. Insurance type', $ctx['payer']['claim_filing_code'] ?? 'CI', 'w2')
            . $box('1a. Insured\'s ID number', $cov['member_id'] ?? '', 'w2')
            . $box('Payer', $c['payer_name'] . ($c['payer_id_code'] ? ' (' . $c['payer_id_code'] . ')' : ''), 'w2')
            . $box('2. Patient\'s name', $pt['last'] . ', ' . $pt['first'], 'w3')
            . $box('3. Patient birth date / sex', $dobFmt($pt['dob']) . '   ' . strtoupper(substr((string)$pt['gender'], 0, 1)), 'w3')
            . $box('4. Insured\'s name', $self ? $pt['last'] . ', ' . $pt['first'] : trim(($cov['subscriber_last'] ?? '') . ', ' . ($cov['subscriber_first'] ?? '')), 'w3')
            . $box('6. Patient relationship to insured', $self ? 'Self' : (string)$cov['relationship'], 'w3')
            . $box('5. Patient\'s address', trim($pt['address'] . ', ' . $pt['city'] . ', ' . $pt['state'] . ' ' . $pt['zip'] . '   Tel: ' . $pt['phone']), 'w3')
            . $box('7. Insured\'s address', $self ? trim($pt['address'] . ', ' . $pt['city'] . ', ' . $pt['state'] . ' ' . $pt['zip']) : '', 'w3')
            . $box('9. Other insured\'s name', $otherName, 'w3')
            . $box('9a/9d. Other insured policy / plan', trim($otherPolicy . '  ' . $otherPlan), 'w3')
            . $box('10. Condition related to employment / accident / other', 'No / No / No', 'w3')
            . $box('11. Insured\'s policy group or FECA number', $cov['group_no'] ?? '', 'w3')
            . $box('11b/11c. Employer / plan name', trim(($cov['employer'] ?? '') . '  ' . ($cov['plan_name'] ?? '')), 'w3')
            . $box('11d. Is there another health benefit plan?', $hasOther ? 'YES' : 'NO', 'w3')
            . $box('21. Diagnosis (ICD Ind. 0)', implode('   ', array_map(fn($d, $i) => chr(65 + $i) . '. ' . $d, $dx, array_keys($dx))), 'w6')
        . '</div>
        <table><thead><tr><th>24 #</th><th>A. Date(s) of service</th><th>B. POS</th><th>D. CPT/HCPCS</th><th>Modifier</th><th>E. Dx pointer</th><th>F. Charges</th><th>G. Units</th><th>J. Rendering NPI</th></tr></thead><tbody>' . $lineRows . '</tbody></table>
        <div class="grid" style="margin-top:10px;">'
            . $box('25. Federal tax ID (EIN)', $fac['tax_id_ein'], 'w2')
            . $box('26. Patient account no.', $c['claim_number'], 'w2')
            . $box('27. Accept assignment?', 'YES', 'w2')
            . $box('28. Total charge', '$' . $usd($totalCharge), 'w2')
            . $box('29. Amount paid (other payers)', '$' . $usd($paidByOthers), 'w2')
            . $box('31. Signature of physician / supplier', trim($prov['first_name'] . ' ' . $prov['last_name']) . '   ' . date('m/d/Y'), 'w2')
            . $box('32. Service facility', trim($fac['facility_name'] . ', ' . ($fac['address_line1'] ?: $fac['address']) . ', ' . $fac['city'] . ', ' . $fac['state'] . ' ' . ($fac['zip_code'] ?: $fac['postal_code'])) . '   NPI: ' . $fac['npi'], 'w3')
            . $box('33. Billing provider', trim(($fac['legal_entity_name'] ?: $fac['facility_name']) . '   NPI: ' . $fac['npi']), 'w3')
        . '</div>
        <div class="note">This is a data sheet organised by CMS-1500 box number for review and manual keying. It is not the red-ink OCR form.</div>
        </body></html>';
    }

    // ------------------------------------------------------------------ payers + 837 settings
    // GET /api/billing/payers
    public function payers(): void {
        $this->checkAccess(self::VIEW_ROLES);
        $this->respond(['status' => 'success', 'data' => Database::fetchAll("SELECT id, name, payer_id_code, claim_filing_code, phone, claim_address, is_active FROM insurance_payers ORDER BY name")]);
    }

    // POST /api/billing/payers  { id?, name, payer_id_code, claim_filing_code, phone, claim_address, is_active }
    public function savePayer(): void {
        $this->checkAccess(self::EDIT_ROLES);
        $in = $this->input();
        $id = (int)($in['id'] ?? 0);
        $name = trim((string)($in['name'] ?? ''));
        $code = trim((string)($in['payer_id_code'] ?? ''));
        $filing = (string)($in['claim_filing_code'] ?? 'CI');
        $phone = trim((string)($in['phone'] ?? ''));
        $addr = trim((string)($in['claim_address'] ?? ''));
        $active = !empty($in['is_active']) || !array_key_exists('is_active', $in) ? 1 : 0;
        if ($name === '') { $this->fail('Payer name is required.'); return; }
        if ($code !== '' && !preg_match('/^[A-Za-z0-9\-]{1,30}$/', $code)) { $this->fail('Payer ID may contain only letters, numbers and dashes.'); return; }
        if (!in_array($filing, ['CI', 'MB', 'MC', 'HM', 'ZZ'], true)) { $this->fail('Choose a valid claim filing type.'); return; }
        $dup = Database::fetch("SELECT id FROM insurance_payers WHERE name = ? AND id <> ?", [$name, $id]);
        if ($dup) { $this->fail('A payer with this name already exists.'); return; }
        if ($id) {
            if (!Database::fetch("SELECT id FROM insurance_payers WHERE id = ?", [$id])) { $this->fail('Payer not found.', 404); return; }
            Database::query("UPDATE insurance_payers SET name = ?, payer_id_code = ?, claim_filing_code = ?, phone = ?, claim_address = ?, is_active = ? WHERE id = ?", [$name, $code ?: null, $filing, $phone ?: null, $addr ?: null, $active, $id]);
        } else {
            Database::query("INSERT INTO insurance_payers (name, payer_id_code, claim_filing_code, phone, claim_address, is_active) VALUES (?, ?, ?, ?, ?, ?)", [$name, $code ?: null, $filing, $phone ?: null, $addr ?: null, $active]);
            $id = (int)Database::lastInsertId();
        }
        $this->audit(null, "Save payer {$name}", $id);
        $this->respond(['status' => 'success', 'message' => 'Payer saved.', 'id' => $id]);
    }

    // GET /api/billing/claim-settings
    public function getSettings(): void {
        $this->checkAccess(self::VIEW_ROLES);
        $this->respond(['status' => 'success', 'data' => $this->settings() + ['clearinghouse_mode' => EdiStore::mode()]]);
    }

    // PUT /api/billing/claim-settings
    public function saveSettings(): void {
        $this->checkAccess(self::EDIT_ROLES);
        $in = $this->input();
        $vals = [];
        foreach (['billing_submitter_id', 'billing_receiver_id'] as $k) {
            $v = strtoupper(trim((string)($in[$k] ?? '')));
            if ($v !== '' && !preg_match('/^[A-Z0-9]{1,15}$/', $v)) { $this->fail('Submitter and receiver IDs are letters/numbers only, up to 15 characters.'); return; }
            $vals[$k] = $v;
        }
        $vals['billing_contact_name'] = mb_substr(trim((string)($in['billing_contact_name'] ?? '')), 0, 60);
        $phone = trim((string)($in['billing_contact_phone'] ?? ''));
        if ($phone !== '' && !preg_match('/^\d{10}$/', preg_replace('/\D/', '', $phone))) { $this->fail('Contact phone must be 10 digits.'); return; }
        $vals['billing_contact_phone'] = preg_replace('/\D/', '', $phone);
        foreach ($vals as $k => $v) {
            Database::query("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?", [$v, $k]);
        }
        $this->audit(null, 'Update 837 submitter settings', 0);
        $this->respond(['status' => 'success', 'message' => '837 settings saved.']);
    }
}
