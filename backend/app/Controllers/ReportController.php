<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

/**
 * ReportController
 * Multi-category EHR analytical reporting engine for Financial, Clinical, Operational, and Summary statistics.
 * Role-Based Access Control:
 * - Super Admin & Admin: Access to all reports.
 * - Billing Staff: Financial, Claims, Billing summaries.
 * - Doctor & Clinical Staff: Clinical encounters, diagnoses, medications, orders, and scheduling reports.
 * - Front Desk: Operational & Scheduling reports.
 */
class ReportController {

    private function checkAuth(): void {
        if (empty($_SESSION['user_id'])) {
            $this->fail(401, 'Unauthenticated session.');
        }
    }

    private function checkAccess(array $allowedRoles): void {
        $this->checkAuth();
        $userRole = $_SESSION['user_role'] ?? '';
        $normalized = strtolower(trim($userRole));
        $allowed = array_map(function($r) { return strtolower(trim($r)); }, $allowedRoles);

        if (!in_array($normalized, $allowed, true) && $normalized !== 'super admin' && $normalized !== 'admin') {
            $this->fail(403, 'Access forbidden for your role.');
        }
    }

    private function fail(int $code, string $message): never {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }

    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['status' => 'success'], $data));
    }

    private function dec($val): string {
        if ($val === null || $val === '') return '';
        try {
            return (string)EncryptionService::decrypt($val);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /**
     * Parse date range filters (from, to). Default to current month.
     */
    private function parseDateRange(): array {
        $from = trim($_GET['from'] ?? '');
        $to = trim($_GET['to'] ?? '');

        if (!$from || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
            $from = date('Y-m-01'); // First day of current month
        }
        if (!$to || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $to = date('Y-m-d'); // Today
        }

        return [$from, $to];
    }

    // =========================================================================
    // 1. GET /api/reports/summary - High-level Practice KPI Cards
    // =========================================================================
    public function summary(): void {
        $this->checkAuth();
        [$from, $to] = $this->parseDateRange();
        $providerId = (int)($_GET['provider_id'] ?? 0);

        // 1. Total Appointments & Attendance
        $apptWhere = ["DATE(start_time) BETWEEN ? AND ?"];
        $apptParams = [$from, $to];
        if ($providerId > 0) {
            $apptWhere[] = "provider_id = ?";
            $apptParams[] = $providerId;
        }
        $apptSql = "SELECT 
                        COUNT(*) as total_appointments,
                        SUM(CASE WHEN status IN ('Completed', 'Checked-In') THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN status = 'No Show' THEN 1 ELSE 0 END) as no_shows,
                        SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                    FROM appointments WHERE " . implode(' AND ', $apptWhere);
        $apptRow = Database::fetch($apptSql, $apptParams) ?: [];

        // 2. Encounters & Documentation
        $encWhere = ["note_date BETWEEN ? AND ?"];
        $encParams = [$from, $to];
        if ($providerId > 0) {
            $encWhere[] = "provider_id = ?";
            $encParams[] = $providerId;
        }
        $encSql = "SELECT 
                       COUNT(*) as total_encounters,
                       SUM(CASE WHEN lock_state = 1 THEN 1 ELSE 0 END) as signed_notes,
                       SUM(CASE WHEN lock_state = 0 OR lock_state IS NULL THEN 1 ELSE 0 END) as unsigned_notes
                   FROM clinical_notes WHERE " . implode(' AND ', $encWhere);
        $encRow = Database::fetch($encSql, $encParams) ?: [];

        // 3. Billing & Revenue KPIs
        $invWhere = ["invoice_date BETWEEN ? AND ?"];
        $invParams = [$from, $to];
        $invSql = "SELECT 
                       COUNT(*) as total_invoices,
                       COALESCE(SUM(total_amount), 0) as gross_charges,
                       COALESCE(SUM(paid_amount), 0) as total_collected,
                       COALESCE(SUM(balance_due), 0) as outstanding_ar
                   FROM invoices WHERE " . implode(' AND ', $invWhere);
        $invRow = Database::fetch($invSql, $invParams) ?: [];

        // 4. Claims Summary
        $claimWhere = ["created_at BETWEEN ? AND ?"];
        $claimParams = [$from . ' 00:00:00', $to . ' 23:59:59'];
        $claimSql = "SELECT 
                         COUNT(*) as total_claims,
                         SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted_claims,
                         SUM(CASE WHEN status = 'Denied' THEN 1 ELSE 0 END) as denied_claims,
                         SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) as pending_claims
                     FROM insurance_claims WHERE " . implode(' AND ', $claimWhere);
        $claimRow = Database::fetch($claimSql, $claimParams) ?: [];

        $this->respond([
            'date_range' => ['from' => $from, 'to' => $to],
            'kpis' => [
                'appointments' => [
                    'total' => (int)($apptRow['total_appointments'] ?? 0),
                    'completed' => (int)($apptRow['completed'] ?? 0),
                    'no_shows' => (int)($apptRow['no_shows'] ?? 0),
                    'cancelled' => (int)($apptRow['cancelled'] ?? 0),
                    'no_show_rate' => ($apptRow['total_appointments'] ?? 0) > 0 
                        ? round((($apptRow['no_shows'] ?? 0) / $apptRow['total_appointments']) * 100, 1) 
                        : 0
                ],
                'clinical' => [
                    'total_encounters' => (int)($encRow['total_encounters'] ?? 0),
                    'signed_notes' => (int)($encRow['signed_notes'] ?? 0),
                    'unsigned_notes' => (int)($encRow['unsigned_notes'] ?? 0),
                    'completion_rate' => ($encRow['total_encounters'] ?? 0) > 0
                        ? round((($encRow['signed_notes'] ?? 0) / $encRow['total_encounters']) * 100, 1)
                        : 0
                ],
                'financial' => [
                    'gross_charges' => (float)($invRow['gross_charges'] ?? 0),
                    'total_collected' => (float)($invRow['total_collected'] ?? 0),
                    'outstanding_ar' => (float)($invRow['outstanding_ar'] ?? 0),
                    'collection_rate' => ($invRow['gross_charges'] ?? 0) > 0
                        ? round((($invRow['total_collected'] ?? 0) / $invRow['gross_charges']) * 100, 1)
                        : 0
                ],
                'claims' => [
                    'total' => (int)($claimRow['total_claims'] ?? 0),
                    'accepted' => (int)($claimRow['accepted_claims'] ?? 0),
                    'denied' => (int)($claimRow['denied_claims'] ?? 0),
                    'pending' => (int)($claimRow['pending_claims'] ?? 0),
                ]
            ]
        ]);
    }

    // =========================================================================
    // 2. GET /api/reports/financial - A/R Aging, Collections & Payments
    // =========================================================================
    public function financial(): void {
        $this->checkAccess(['Super Admin', 'Admin', 'Billing Staff', 'Doctor']);
        [$from, $to] = $this->parseDateRange();

        // 1. A/R Aging Buckets
        $agingSql = "SELECT 
            SUM(CASE WHEN DATEDIFF(CURDATE(), invoice_date) <= 30 THEN balance_due ELSE 0 END) AS bucket_0_30,
            SUM(CASE WHEN DATEDIFF(CURDATE(), invoice_date) BETWEEN 31 AND 60 THEN balance_due ELSE 0 END) AS bucket_31_60,
            SUM(CASE WHEN DATEDIFF(CURDATE(), invoice_date) BETWEEN 61 AND 90 THEN balance_due ELSE 0 END) AS bucket_61_90,
            SUM(CASE WHEN DATEDIFF(CURDATE(), invoice_date) BETWEEN 91 AND 120 THEN balance_due ELSE 0 END) AS bucket_91_120,
            SUM(CASE WHEN DATEDIFF(CURDATE(), invoice_date) > 120 THEN balance_due ELSE 0 END) AS bucket_120_plus,
            SUM(balance_due) AS total_ar
        FROM invoices 
        WHERE status <> 'Paid' AND balance_due > 0";
        $aging = Database::fetch($agingSql) ?: [
            'bucket_0_30' => 0, 'bucket_31_60' => 0, 'bucket_61_90' => 0, 
            'bucket_91_120' => 0, 'bucket_120_plus' => 0, 'total_ar' => 0
        ];

        // 2. Revenue by Payment Method
        $paySql = "SELECT 
                       payment_method, 
                       COUNT(*) as transaction_count, 
                       COALESCE(SUM(amount), 0) as total_amount
                   FROM payments 
                   WHERE payment_date BETWEEN ? AND ? AND voided_at IS NULL
                   GROUP BY payment_method ORDER BY total_amount DESC";
        $payRows = Database::fetchAll($paySql, [$from, $to]);

        // 3. Invoices List for Selected Period
        $invSql = "SELECT i.id, i.invoice_number, i.invoice_date, i.patient_id, i.total_amount, i.paid_amount, i.balance_due, i.status,
                          p.first_name_encrypted, p.last_name_encrypted
                   FROM invoices i
                   JOIN patients p ON i.patient_id = p.id
                   WHERE i.invoice_date BETWEEN ? AND ?
                   ORDER BY i.invoice_date DESC LIMIT 100";
        $rawInvoices = Database::fetchAll($invSql, [$from, $to]);
        $invoices = [];
        foreach ($rawInvoices as $r) {
            $invoices[] = [
                'id' => $r['id'],
                'invoice_number' => $r['invoice_number'],
                'invoice_date' => $r['invoice_date'],
                'patient_id' => $r['patient_id'],
                'patient_name' => trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted'])),
                'total_amount' => (float)$r['total_amount'],
                'paid_amount' => (float)$r['paid_amount'],
                'balance_due' => (float)$r['balance_due'],
                'status' => $r['status']
            ];
        }

        // 4. Claims Status Breakdown
        $claimsSql = "SELECT status, COUNT(*) as count, COALESCE(SUM(total_charge), 0) as total_charge
                      FROM insurance_claims
                      WHERE DATE(created_at) BETWEEN ? AND ?
                      GROUP BY status";
        $claimsBreakdown = Database::fetchAll($claimsSql, [$from, $to]);

        $this->respond([
            'date_range' => ['from' => $from, 'to' => $to],
            'ar_aging' => [
                'bucket_0_30' => (float)($aging['bucket_0_30'] ?? 0),
                'bucket_31_60' => (float)($aging['bucket_31_60'] ?? 0),
                'bucket_61_90' => (float)($aging['bucket_61_90'] ?? 0),
                'bucket_91_120' => (float)($aging['bucket_91_120'] ?? 0),
                'bucket_120_plus' => (float)($aging['bucket_120_plus'] ?? 0),
                'total_ar' => (float)($aging['total_ar'] ?? 0),
            ],
            'payment_methods' => $payRows,
            'claims_breakdown' => $claimsBreakdown,
            'invoices' => $invoices
        ]);
    }

    // =========================================================================
    // 3. GET /api/reports/clinical - Encounters, Diagnoses, Orders
    // =========================================================================
    public function clinical(): void {
        $this->checkAccess(['Super Admin', 'Admin', 'Doctor', 'Nurse', 'Medical Assistant', 'Billing Staff']);
        [$from, $to] = $this->parseDateRange();
        $providerId = (int)($_GET['provider_id'] ?? 0);
        $encounterType = trim($_GET['encounter_type'] ?? '');
        $status = trim($_GET['status'] ?? ''); // 'signed', 'draft', or empty
        $q = trim($_GET['q'] ?? '');

        $where = ["cn.note_date BETWEEN ? AND ?"];
        $params = [$from, $to];
        if ($providerId > 0) {
            $where[] = "cn.provider_id = ?";
            $params[] = $providerId;
        }
        if ($encounterType !== '') {
            $where[] = "cn.encounter_type = ?";
            $params[] = $encounterType;
        }
        if ($status === 'signed') {
            $where[] = "cn.lock_state = 1";
        } elseif ($status === 'draft') {
            $where[] = "(cn.lock_state = 0 OR cn.lock_state IS NULL)";
        }

        // 1. Encounter volume by provider
        $provSql = "SELECT u.id as provider_id, u.first_name, u.last_name,
                           COUNT(cn.id) as total_encounters,
                           SUM(CASE WHEN cn.lock_state = 1 THEN 1 ELSE 0 END) as signed_count,
                           SUM(CASE WHEN cn.lock_state = 0 OR cn.lock_state IS NULL THEN 1 ELSE 0 END) as unsigned_count
                    FROM clinical_notes cn
                    JOIN users u ON cn.provider_id = u.id
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY u.id, u.first_name, u.last_name
                    ORDER BY total_encounters DESC";
        $providerBreakdown = Database::fetchAll($provSql, $params);

        // 2. Encounters list with details
        $encListSql = "SELECT cn.id, cn.note_date, cn.encounter_type, cn.lock_state, cn.chief_complaint, cn.icd10_codes,
                              u.first_name as prov_fname, u.last_name as prov_lname,
                              p.id as patient_id, p.first_name_encrypted, p.last_name_encrypted
                       FROM clinical_notes cn
                       JOIN users u ON cn.provider_id = u.id
                       JOIN patients p ON cn.patient_id = p.id
                       WHERE " . implode(' AND ', $where) . "
                       ORDER BY cn.note_date DESC LIMIT 150";
        $rawEncounters = Database::fetchAll($encListSql, $params);
        $encounters = [];
        $icdMap = [];

        $qLower = strtolower($q);

        foreach ($rawEncounters as $r) {
            $pName = trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted']));
            $docName = 'Dr. ' . $r['prov_fname'] . ' ' . $r['prov_lname'];
            $complaint = $r['chief_complaint'] ?? '';
            $encType = $r['encounter_type'] ?: 'Standard Visit';
            $isSigned = ((int)($r['lock_state'] ?? 0) === 1);

            // If search query is provided, check patient name, provider name, or chief complaint
            if ($qLower !== '') {
                $haystack = strtolower($pName . ' ' . $docName . ' ' . $complaint . ' ' . $encType);
                if (strpos($haystack, $qLower) === false) {
                    continue;
                }
            }

            $encounters[] = [
                'id' => $r['id'],
                'note_date' => $r['note_date'],
                'encounter_type' => $encType,
                'provider' => $docName,
                'provider_id' => $r['prov_fname'] ? (int)$r['prov_fname'] : 0,
                'patient_id' => $r['patient_id'],
                'patient_name' => $pName ?: ('Patient #' . $r['patient_id']),
                'lock_state' => (int)($r['lock_state'] ?? 0),
                'status' => $isSigned ? 'Signed' : 'Unsigned Draft',
                'chief_complaint' => $complaint
            ];

            // Extract ICD-10 frequencies
            if (!empty($r['icd10_codes'])) {
                $lines = preg_split('/[\r\n;,]+/', $r['icd10_codes']);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!$line) continue;
                    if (preg_match('/^([A-Z][0-9][0-9A-Z]?(?:\.[0-9A-Z]{1,4})?)(?:\s*[-:\s]\s*(.*))?$/i', $line, $m)) {
                        $code = strtoupper(trim($m[1]));
                        $desc = trim($m[2] ?? '');
                        if (!isset($icdMap[$code])) {
                            $icdMap[$code] = ['code' => $code, 'description' => $desc ?: $code, 'count' => 0];
                        }
                        $icdMap[$code]['count']++;
                    }
                }
            }
        }

        // Sort top diagnoses
        uasort($icdMap, function($a, $b) { return $b['count'] <=> $a['count']; });
        $topDiagnoses = array_slice(array_values($icdMap), 0, 20);

        // 3. Orders Overview for period
        $ordersSql = "SELECT order_type, status, COUNT(*) as count 
                      FROM orders 
                      WHERE DATE(created_at) BETWEEN ? AND ?
                      GROUP BY order_type, status";
        $orderStats = Database::fetchAll($ordersSql, [$from, $to]);

        // 4. Provider list for dropdown filter
        $allProviders = Database::fetchAll("SELECT id, first_name, last_name FROM users WHERE role IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant') AND is_active = 1 ORDER BY first_name ASC");

        // Metrics calculations
        $totalEnc = count($encounters);
        $signedCount = 0;
        $draftCount = 0;
        foreach ($encounters as $e) {
            if ($e['lock_state'] === 1) $signedCount++;
            else $draftCount++;
        }
        $docRate = $totalEnc > 0 ? round(($signedCount / $totalEnc) * 100) : 0;

        $this->respond([
            'date_range' => ['from' => $from, 'to' => $to],
            'metrics' => [
                'total_encounters' => $totalEnc,
                'signed_count' => $signedCount,
                'draft_count' => $draftCount,
                'documentation_rate' => $docRate
            ],
            'providers_list' => array_map(function($p) {
                return ['id' => $p['id'], 'name' => 'Dr. ' . $p['first_name'] . ' ' . $p['last_name']];
            }, $allProviders),
            'provider_breakdown' => $providerBreakdown,
            'top_diagnoses' => $topDiagnoses,
            'order_stats' => $orderStats,
            'encounters' => $encounters
        ]);
    }

    // =========================================================================
    // 4. GET /api/reports/operations - Appointments, Utilization, No-shows
    // =========================================================================
    public function operations(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        [$from, $to] = $this->parseDateRange();
        $providerId = (int)($_GET['provider_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $visitType = trim($_GET['visit_type'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $where = ["DATE(a.start_time) BETWEEN ? AND ?"];
        $params = [$from, $to];
        if ($providerId > 0) {
            $where[] = "a.provider_id = ?";
            $params[] = $providerId;
        }
        if ($status !== '') {
            $where[] = "a.status = ?";
            $params[] = $status;
        }
        if ($visitType !== '') {
            $where[] = "a.visit_type = ?";
            $params[] = $visitType;
        }

        // 1. Status Breakdown
        $statusSql = "SELECT a.status, COUNT(*) as count 
                      FROM appointments a 
                      WHERE " . implode(' AND ', $where) . " 
                      GROUP BY a.status";
        $statusRows = Database::fetchAll($statusSql, $params);

        // 2. Doctor Utilization (Volume & Completed)
        $docSql = "SELECT u.id as provider_id, u.first_name, u.last_name,
                          COUNT(a.id) as total_appointments,
                          SUM(CASE WHEN a.status IN ('Completed', 'Checked-In') THEN 1 ELSE 0 END) as completed,
                          SUM(CASE WHEN a.status = 'No Show' THEN 1 ELSE 0 END) as no_shows,
                          SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                   FROM appointments a
                   JOIN users u ON a.provider_id = u.id
                   WHERE " . implode(' AND ', $where) . "
                   GROUP BY u.id, u.first_name, u.last_name
                   ORDER BY total_appointments DESC";
        $doctorUtilization = Database::fetchAll($docSql, $params);

        // 3. Appointments List
        $apptListSql = "SELECT a.id, a.start_time, a.end_time, a.status, a.notes as reason, a.visit_type,
                               u.first_name as doc_first, u.last_name as doc_last,
                               p.id as patient_id, p.first_name_encrypted, p.last_name_encrypted
                        FROM appointments a
                        JOIN users u ON a.provider_id = u.id
                        JOIN patients p ON a.patient_id = p.id
                        WHERE " . implode(' AND ', $where) . "
                        ORDER BY a.start_time DESC LIMIT 150";
        $rawAppts = Database::fetchAll($apptListSql, $params);
        $appointments = [];
        $qLower = strtolower($q);

        foreach ($rawAppts as $r) {
            $pName = trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted']));
            $docName = 'Dr. ' . $r['doc_first'] . ' ' . $r['doc_last'];
            $reason = $r['reason'] ?? '';
            $vType = $r['visit_type'] ?? 'Regular';

            if ($qLower !== '') {
                $haystack = strtolower($pName . ' ' . $docName . ' ' . $reason . ' ' . $vType . ' ' . $r['status']);
                if (strpos($haystack, $qLower) === false) {
                    continue;
                }
            }

            $appointments[] = [
                'id' => $r['id'],
                'start_time' => $r['start_time'],
                'end_time' => $r['end_time'],
                'status' => $r['status'],
                'reason' => $reason,
                'visit_type' => $vType,
                'provider' => $docName,
                'patient_id' => $r['patient_id'],
                'patient_name' => $pName ?: ('Patient #' . $r['patient_id'])
            ];
        }

        // Summary KPI Metrics
        $totalBooked = count($appointments);
        $completedCount = 0;
        $noShowCount = 0;
        $cancelledCount = 0;
        foreach ($appointments as $a) {
            $s = strtolower($a['status']);
            if (in_array($s, ['completed', 'checked-in'])) $completedCount++;
            elseif ($s === 'no show') $noShowCount++;
            elseif ($s === 'cancelled') $cancelledCount++;
        }

        // Providers list for filter dropdown
        $allProviders = Database::fetchAll("SELECT id, first_name, last_name FROM users WHERE role IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant') AND is_active = 1 ORDER BY first_name ASC");

        $this->respond([
            'date_range' => ['from' => $from, 'to' => $to],
            'metrics' => [
                'total_booked' => $totalBooked,
                'completed' => $completedCount,
                'no_shows' => $noShowCount,
                'cancelled' => $cancelledCount
            ],
            'providers_list' => array_map(function($p) {
                return ['id' => $p['id'], 'name' => 'Dr. ' . $p['first_name'] . ' ' . $p['last_name']];
            }, $allProviders),
            'status_breakdown' => $statusRows,
            'doctor_utilization' => $doctorUtilization,
            'appointments' => $appointments
        ]);
    }

    // =========================================================================
    // 6. GET /api/reports/users - Staff & User Activity / Roles Breakdown
    // =========================================================================
    public function users(): void {
        $this->checkAccess(['Super Admin', 'Admin', 'Doctor']);

        $category = trim($_GET['category'] ?? ''); // 'providers', 'staff', or all
        $q = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if ($category === 'providers') {
            $where[] = "role IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant')";
        } elseif ($category === 'staff') {
            $where[] = "role NOT IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant')";
        }

        if ($q !== '') {
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $like = '%' . $q . '%';
            $params = array_merge($params, [$like, $like, $like, $like, $like]);
        }

        $whereSql = implode(' AND ', $where);
        $userList = Database::fetchAll("SELECT id, username, first_name, last_name, email, role, specialty, phone, work_phone, is_active, last_login, created_at FROM users WHERE {$whereSql} ORDER BY first_name ASC", $params);

        $counts = Database::fetchAll("SELECT 
            SUM(CASE WHEN role IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant') THEN 1 ELSE 0 END) as provider_count,
            SUM(CASE WHEN role NOT IN ('Doctor', 'Nurse Practitioner', 'Physician Assistant') THEN 1 ELSE 0 END) as staff_count,
            COUNT(*) as total_count
        FROM users WHERE is_active = 1");

        $this->respond([
            'counts' => $counts[0] ?? ['provider_count' => 0, 'staff_count' => 0, 'total_count' => 0],
            'users' => $userList
        ]);
    }

    // =========================================================================
    // 7. GET /api/reports/specialties - Encounters & Volume by Specialty
    // =========================================================================
    public function specialties(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        [$from, $to] = $this->parseDateRange();

        $sql = "SELECT 
                    COALESCE(u.specialty, 'General / Unassigned') as specialty,
                    COUNT(cn.id) as total_encounters,
                    COUNT(DISTINCT cn.patient_id) as unique_patients,
                    SUM(CASE WHEN cn.lock_state = 1 THEN 1 ELSE 0 END) as signed_encounters,
                    COUNT(DISTINCT u.id) as provider_count
                FROM users u
                LEFT JOIN clinical_notes cn ON cn.provider_id = u.id AND cn.note_date BETWEEN ? AND ?
                WHERE u.role IN ('Doctor', 'Nurse', 'Medical Assistant')
                GROUP BY specialty
                ORDER BY total_encounters DESC";
        $specialtyBreakdown = Database::fetchAll($sql, [$from, $to]);

        $this->respond([
            'date_range' => ['from' => $from, 'to' => $to],
            'specialty_breakdown' => $specialtyBreakdown
        ]);
    }

    // =========================================================================
    // 5. GET /api/reports/export - Universal Filtered CSV Exporter
    // =========================================================================
    public function export(): void {
        $this->checkAuth();
        $type = trim($_GET['type'] ?? 'clinical');
        [$from, $to] = $this->parseDateRange();

        $filename = "ehr_report_{$type}_{$from}_to_{$to}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        if ($type === 'clinical') {
            $this->checkAccess(['Super Admin', 'Admin', 'Doctor', 'Nurse', 'Billing Staff']);
            fputcsv($out, ['Encounter ID', 'Note Date', 'Patient ID', 'Patient Name', 'Provider', 'Encounter Type', 'Documentation Status', 'Chief Complaint']);

            $sql = "SELECT cn.id, cn.note_date, cn.encounter_type, cn.lock_state, cn.chief_complaint,
                           u.first_name as prov_fname, u.last_name as prov_lname,
                           p.id as patient_id, p.first_name_encrypted, p.last_name_encrypted
                    FROM clinical_notes cn
                    JOIN users u ON cn.provider_id = u.id
                    JOIN patients p ON cn.patient_id = p.id
                    WHERE cn.note_date BETWEEN ? AND ?
                    ORDER BY cn.note_date DESC";
            $rows = Database::fetchAll($sql, [$from, $to]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['note_date'],
                    $r['patient_id'],
                    trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted'])),
                    'Dr. ' . $r['prov_fname'] . ' ' . $r['prov_lname'],
                    $r['encounter_type'] ?: 'Standard Visit',
                    ((int)$r['lock_state'] === 1) ? 'Signed & Locked' : 'Draft / Unsigned',
                    $r['chief_complaint'] ?? ''
                ]);
            }
        } elseif ($type === 'appointments' || $type === 'operations') {
            $this->checkAccess(Roles::ALL_STAFF);
            fputcsv($out, ['Appointment ID', 'Date & Time', 'Patient ID', 'Patient Name', 'Provider', 'Visit Type', 'Status', 'Reason']);

            $sql = "SELECT a.id, a.start_time, a.status, a.reason, a.visit_type,
                           u.first_name as doc_first, u.last_name as doc_last,
                           p.id as patient_id, p.first_name_encrypted, p.last_name_encrypted
                    FROM appointments a
                    JOIN users u ON a.provider_id = u.id
                    JOIN patients p ON a.patient_id = p.id
                    WHERE DATE(a.start_time) BETWEEN ? AND ?
                    ORDER BY a.start_time DESC";
            $rows = Database::fetchAll($sql, [$from, $to]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['start_time'],
                    $r['patient_id'],
                    trim($this->dec($r['first_name_encrypted']) . ' ' . $this->dec($r['last_name_encrypted'])),
                    'Dr. ' . $r['doc_first'] . ' ' . $r['doc_last'],
                    $r['visit_type'] ?? 'Regular',
                    $r['status'],
                    $r['reason'] ?? ''
                ]);
            }
        } elseif ($type === 'users') {
            $this->checkAccess(['Super Admin', 'Admin', 'Doctor']);
            fputcsv($out, ['User ID', 'Username', 'Name', 'Email', 'Role', 'Specialty', 'Status', 'Last Login']);

            $rows = Database::fetchAll("SELECT id, username, first_name, last_name, email, role, specialty, is_active, last_login FROM users ORDER BY role, first_name ASC");
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['username'],
                    $r['first_name'] . ' ' . $r['last_name'],
                    $r['email'],
                    $r['role'],
                    $r['specialty'] ?? 'N/A',
                    $r['is_active'] ? 'Active' : 'Inactive',
                    $r['last_login'] ?? 'Never'
                ]);
            }
        } elseif ($type === 'specialties') {
            $this->checkAccess(Roles::ALL_STAFF);
            fputcsv($out, ['Specialty', 'Provider Count', 'Total Encounters', 'Unique Patients', 'Signed Encounters']);

            $sql = "SELECT 
                        COALESCE(u.specialty, 'General / Unassigned') as specialty,
                        COUNT(cn.id) as total_encounters,
                        COUNT(DISTINCT cn.patient_id) as unique_patients,
                        SUM(CASE WHEN cn.lock_state = 1 THEN 1 ELSE 0 END) as signed_encounters,
                        COUNT(DISTINCT u.id) as provider_count
                    FROM users u
                    LEFT JOIN clinical_notes cn ON cn.provider_id = u.id AND cn.note_date BETWEEN ? AND ?
                    WHERE u.role IN ('Doctor', 'Nurse', 'Medical Assistant')
                    GROUP BY specialty
                    ORDER BY total_encounters DESC";
            $rows = Database::fetchAll($sql, [$from, $to]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['specialty'],
                    $r['provider_count'],
                    $r['total_encounters'],
                    $r['unique_patients'],
                    $r['signed_encounters']
                ]);
            }
        }

        fclose($out);
        exit();
    }
}
