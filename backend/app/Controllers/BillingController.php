<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;
use App\Services\AuditLogger;

class BillingController {
    private function checkAccess(array $allowedRoles): void {
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    private function decryptPatientName(array $row): string {
        return EncryptionService::decrypt($row['first_name_encrypted']) . ' ' . EncryptionService::decrypt($row['last_name_encrypted']);
    }

    // GET /api/billing/unbilled-encounters
    public function unbilledEncounters(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $patientId = $_GET['patient_id'] ?? null;
        $where = ["cn.id NOT IN (SELECT encounter_id FROM invoices WHERE encounter_id IS NOT NULL)"];
        $params = [];
        if ($patientId) {
            $where[] = "cn.patient_id = ?";
            $params[] = $patientId;
        }
        $whereSql = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT cn.id, cn.note_date, cn.encounter_type, cn.icd10_codes, cn.chief_complaint,
                       cn.provider_id, u.first_name AS prov_fname, u.last_name AS prov_lname,
                       p.id AS patient_id, p.first_name_encrypted, p.last_name_encrypted
                FROM clinical_notes cn
                JOIN patients p ON cn.patient_id = p.id
                JOIN users u ON cn.provider_id = u.id
                {$whereSql}
                ORDER BY cn.note_date DESC";

        $rows = Database::fetchAll($sql, $params);
        $result = [];
        foreach ($rows as $r) {
            $cptCodesOnly = [];
            $rawCodes = trim($r['icd10_codes'] ?? '');
            if ($rawCodes) {
                $rawItems = preg_split('/[\r\n;]+/', $rawCodes);
                foreach ($rawItems as $item) {
                    $item = trim($item);
                    if (!$item) continue;
                    // Match 5-digit CPT-4 codes (e.g. 99213, 99214, 99395, 90471) or HCPCS codes (e.g. G0101, G0438)
                    if (preg_match('/^([0-9]{5}|[A-Z][0-9]{4})\b/i', $item, $m)) {
                        $cptCodesOnly[] = $item;
                    } else if (preg_match('/\b([0-9]{5})\b/', $item, $m)) {
                        $cptCodesOnly[] = $m[1];
                    }
                }
            }

            $result[] = [
                'encounter_id'   => $r['id'],
                'patient_id'     => $r['patient_id'],
                'patient_name'   => $this->decryptPatientName($r),
                'encounter_date' => $r['note_date'],
                'encounter_type' => $r['encounter_type'],
                'cpt4_codes'     => !empty($cptCodesOnly) ? implode('; ', array_unique($cptCodesOnly)) : '',
                'icd10_codes'    => $r['icd10_codes'],
                'chief_complaint'=> $r['chief_complaint'],
                'provider'       => 'Dr. ' . $r['prov_fname'] . ' ' . $r['prov_lname'],
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    // GET /api/billing/encounter/{id}/charges
    public function encounterCharges(array $params): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $encId = $params['id'] ?? null;
        if (!$encId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        $enc = Database::fetch(
            "SELECT cn.*, p.first_name_encrypted, p.last_name_encrypted, p.dob_encrypted,
                    u.first_name AS prov_fname, u.last_name AS prov_lname
             FROM clinical_notes cn
             JOIN patients p ON cn.patient_id = p.id
             JOIN users u ON cn.provider_id = u.id
             WHERE cn.id = ?",
            [$encId]
        );

        if (!$enc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Encounter not found.']);
            return;
        }

        // Parse CPT-4 procedure codes / ICD-10 codes from encounter
        $icd10Raw = trim($enc['icd10_codes'] ?? '');
        $lines = [];
        if ($icd10Raw) {
            $rawItems = preg_split('/[\r\n]+/', $icd10Raw);
            $parsedEntries = [];
            foreach ($rawItems as $rawLine) {
                $rawLine = trim($rawLine);
                if (!$rawLine) continue;
                if (preg_match('/^([0-9]{5}|[A-Z][0-9A-Z]{2,4})(?:\s*[-:\s]\s*(.*))?$/i', $rawLine, $m)) {
                    $cOnly = strtoupper(trim($m[1]));
                    $cDesc = isset($m[2]) ? trim($m[2]) : '';
                    $parsedEntries[] = ['code' => $cOnly, 'desc' => $cDesc];
                } else if (preg_match_all('/([0-9]{5}|[A-Z][0-9]{2}(?:\.[0-9A-Z]{1,4})?)(?:\s*[-:\s]\s*([^,\n\r;\n]+))?/i', $rawLine, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $cOnly = strtoupper(trim($m[1]));
                        $cDesc = isset($m[2]) ? trim($m[2]) : '';
                        $parsedEntries[] = ['code' => $cOnly, 'desc' => $cDesc];
                    }
                } else {
                    $parts = explode('-', $rawLine, 2);
                    $parsedEntries[] = [
                        'code' => trim($parts[0]),
                        'desc' => isset($parts[1]) ? trim($parts[1]) : ''
                    ];
                }
            }

            // ICD-10 to CPT mapping (common primary care mappings)
            $icdToCpt = [
                'Z00' => '99395', 'Z00.00' => '99395', 'Z00.01' => '99395',
                'E78' => '99213', 'E78.5' => '99213', 'E78.0' => '99213', 'E78.2' => '99213',
                'R05' => '99213', 'R05.9' => '99213',
                'R50' => '99213', 'R50.9' => '99213',
                'J06' => '99213', 'J06.9' => '99213',
                'J02' => '99213', 'J02.9' => '99213',
                'J18' => '99215', 'J18.9' => '99215',
                'I10' => '99213',
                'E11' => '99214', 'E11.9' => '99214', 'E11.65' => '99214',
                'E10' => '99214', 'E10.9' => '99214',
                'J45' => '99213', 'J45.909' => '99213',
                'M54' => '99213', 'M54.5' => '99213', 'M54.50' => '99213',
                'F41' => '99214', 'F41.1' => '99214',
                'F32' => '99214', 'F32.9' => '99214',
                'K21' => '99213', 'K21.0' => '99213',
                'N39' => '99213', 'N39.0' => '99213',
                'G44' => '99213', 'G44.209' => '99213',
                'E66' => '99213', 'E66.9' => '99213',
                'R07' => '99214', 'R07.9' => '99214',
                'Z23'  => '90471',
                'Z30'  => 'G0101',
                'Z34'  => '59400',
            ];

            foreach ($parsedEntries as $entry) {
                $codeOnly = $entry['code'];
                $codeDesc = $entry['desc'];

                // Check if code is directly a CPT-4 code in cpt_charge_master
                $cptRow = Database::fetch("SELECT * FROM cpt_charge_master WHERE cpt_code = ?", [$codeOnly]);

                if ($cptRow) {
                    $suggestedCpt = $cptRow['cpt_code'];
                    $category = $cptRow['category'] ?? 'Primary Care';
                    $defaultCharge = (float)($cptRow['default_charge'] ?? 75.00);
                    $cptDesc = !empty($codeDesc) ? $codeDesc : $cptRow['description'];
                } else {
                    $prefix = substr($codeOnly, 0, 3);
                    $suggestedCpt = $icdToCpt[$codeOnly] ?? ($icdToCpt[$prefix] ?? '99213');
                    $cptRow = Database::fetch("SELECT * FROM cpt_charge_master WHERE cpt_code = ?", [$suggestedCpt]);
                    $category = $cptRow ? $cptRow['category'] : 'Primary Care';
                    $defaultCharge = $cptRow ? (float)$cptRow['default_charge'] : 75.00;
                    $cptDesc = $cptRow ? $cptRow['description'] : 'Primary Care Office Visit';
                }

                $lines[] = [
                    'icd10_code'        => $codeOnly,
                    'icd10_description' => $codeDesc,
                    'cpt_code'          => $suggestedCpt,
                    'cpt_description'   => $cptDesc,
                    'quantity'          => 1,
                    'unit_price'        => $defaultCharge,
                    'total_price'       => $defaultCharge,
                ];
            }
        }

        if (empty($lines)) {
            $lines[] = [
                'icd10_code'        => 'Z00.00',
                'icd10_description' => 'General Medical Examination',
                'cpt_code'          => '99213',
                'cpt_description'   => 'Office or Outpatient Visit (Expanded)',
                'quantity'          => 1,
                'unit_price'        => 100.00,
                'total_price'       => 100.00,
            ];
        }

        echo json_encode([
            'status' => 'success',
            'encounter' => [
                'id'             => $enc['id'],
                'patient_id'     => $enc['patient_id'],
                'patient_name'   => $this->decryptPatientName($enc),
                'encounter_date' => $enc['note_date'],
                'encounter_type' => $enc['encounter_type'],
                'provider'       => 'Dr. ' . $enc['prov_fname'] . ' ' . $enc['prov_lname'],
                'icd10_codes'    => $enc['icd10_codes'],
            ],
            'line_items' => $lines,
        ]);
    }

    // GET /api/billing/cpt-codes
    public function cptCodes(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
        header('Content-Type: application/json');

        $category = trim($_GET['category'] ?? '');
        
        if ($category !== '') {
            $catLower = strtolower($category);
            if (strpos($catLower, 'gyn') !== false || strpos($catLower, 'ob') !== false) {
                $cpts = Database::fetchAll("SELECT * FROM cpt_charge_master WHERE category LIKE '%OB%' OR category LIKE '%GYN%' ORDER BY cpt_code");
            } elseif (strpos($catLower, 'ped') !== false) {
                $cpts = Database::fetchAll("SELECT * FROM cpt_charge_master WHERE category LIKE '%ped%' ORDER BY cpt_code");
            } elseif (strpos($catLower, 'primary') !== false || strpos($catLower, 'general') !== false) {
                $cpts = Database::fetchAll("SELECT * FROM cpt_charge_master WHERE category LIKE '%primary%' OR category LIKE '%general%' OR category LIKE '%E&M%' ORDER BY cpt_code");
            } else {
                $cpts = Database::fetchAll("SELECT * FROM cpt_charge_master WHERE category LIKE ? ORDER BY cpt_code", ['%' . $category . '%']);
            }
        } else {
            $cpts = Database::fetchAll("SELECT * FROM cpt_charge_master ORDER BY category, cpt_code");
        }

        echo json_encode(['status' => 'success', 'data' => $cpts]);
    }

    // POST /api/billing/cpt-codes
    public function storeCptCode(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff']);
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        $code = trim($input['cpt_code'] ?? '');
        $desc = trim($input['description'] ?? '');
        $charge = (float)($input['default_charge'] ?? 0);
        $cat = trim($input['category'] ?? 'E&M');
        if (!$code || !$desc || $charge <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'CPT code, description and charge are required.']);
            return;
        }
        Database::query(
            "INSERT INTO cpt_charge_master (cpt_code, description, default_charge, category) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE description=VALUES(description), default_charge=VALUES(default_charge), category=VALUES(category)",
            [$code, $desc, $charge, $cat]
        );
        echo json_encode(['status' => 'success', 'message' => 'CPT code saved.']);
    }

    // POST /api/billing/invoices
    public function storeInvoice(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $patientId   = $input['patient_id'] ?? null;
        $encounterId = $input['encounter_id'] ?? null;
        $invoiceDate = $input['invoice_date'] ?? date('Y-m-d');
        $dueDate     = $input['due_date'] ?? date('Y-m-d', strtotime('+30 days'));
        $discount    = (float)($input['discount'] ?? 0);
        $notes       = $input['notes'] ?? '';
        $lineItems   = $input['line_items'] ?? [];

        if (!$patientId || empty($lineItems)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID and at least one line item required.']);
            return;
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($lineItems as &$item) {
            $item['quantity']    = max(1, (int)($item['quantity'] ?? 1));
            $item['unit_price']  = (float)($item['unit_price'] ?? 0);
            $item['total_price'] = round($item['quantity'] * $item['unit_price'], 2);
            $subtotal += $item['total_price'];
        }
        $total = round($subtotal - $discount, 2);

        // Generate invoice number
        $lastInv = Database::fetch("SELECT MAX(id) AS max_id FROM invoices");
        $nextId  = ($lastInv['max_id'] ?? 0) + 1;
        $invNumber = 'INV-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

        // Insert invoice
        Database::query(
            "INSERT INTO invoices (patient_id, encounter_id, invoice_number, invoice_date, due_date, subtotal, discount, total_amount, paid_amount, status, notes, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, 'Issued', ?, ?)",
            [$patientId, $encounterId ?: null, $invNumber, $invoiceDate, $dueDate, $subtotal, $discount, $total, $notes, $_SESSION['user_id']]
        );
        $invoiceId = Database::lastInsertId();

        // Insert line items
        foreach ($lineItems as $item) {
            Database::query(
                "INSERT INTO invoice_line_items (invoice_id, icd10_code, icd10_description, cpt_code, cpt_description, quantity, unit_price, total_price)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $invoiceId,
                    $item['icd10_code'] ?? null,
                    $item['icd10_description'] ?? null,
                    $item['cpt_code'] ?? null,
                    $item['cpt_description'] ?? null,
                    $item['quantity'],
                    $item['unit_price'],
                    $item['total_price'],
                ]
            );
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Create Invoice', 'Billing', $invoiceId);

        echo json_encode(['status' => 'success', 'message' => 'Invoice created successfully.', 'invoice_id' => $invoiceId, 'invoice_number' => $invNumber]);
    }

    // GET /api/billing/invoices
    public function listInvoices(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $patientId = $_GET['patient_id'] ?? null;
        $whereSql = "";
        $params = [];
        if ($patientId) {
            $whereSql = "WHERE i.patient_id = ?";
            $params[] = $patientId;
        }

        $sql = "SELECT i.*, p.first_name_encrypted, p.last_name_encrypted,
                       u.first_name AS created_fname, u.last_name AS created_lname
                FROM invoices i
                JOIN patients p ON i.patient_id = p.id
                JOIN users u ON i.created_by = u.id
                {$whereSql}
                ORDER BY i.created_at DESC";

        $rows = Database::fetchAll($sql, $params);
        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'id'             => $r['id'],
                'patient_id'     => $r['patient_id'],
                'patient_name'   => $this->decryptPatientName($r),
                'encounter_id'   => $r['encounter_id'],
                'invoice_number' => $r['invoice_number'],
                'invoice_date'   => $r['invoice_date'],
                'due_date'       => $r['due_date'],
                'subtotal'       => $r['subtotal'],
                'discount'       => $r['discount'],
                'total_amount'   => $r['total_amount'],
                'paid_amount'    => $r['paid_amount'],
                'balance'        => round($r['total_amount'] - $r['paid_amount'], 2),
                'status'         => $r['status'],
                'notes'          => $r['notes'],
                'created_by'     => 'Dr. ' . $r['created_fname'] . ' ' . $r['created_lname'],
                'created_at'     => $r['created_at'],
            ];
        }

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    // GET /api/billing/invoice/{id}
    public function getInvoice(array $params): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invoice ID required.']);
            return;
        }

        $inv = Database::fetch(
            "SELECT i.*, p.first_name_encrypted, p.last_name_encrypted, p.dob_encrypted, p.phone_encrypted,
                    u.first_name AS created_fname, u.last_name AS created_lname,
                    cn.note_date AS encounter_date, pu.first_name AS prov_fname, pu.last_name AS prov_lname
             FROM invoices i
             JOIN patients p ON i.patient_id = p.id
             JOIN users u ON i.created_by = u.id
             LEFT JOIN clinical_notes cn ON cn.id = i.encounter_id
             LEFT JOIN users pu ON pu.id = cn.provider_id
             WHERE i.id = ?",
            [$id]
        );

        if (!$inv) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            return;
        }

        $lineItems = Database::fetchAll(
            "SELECT * FROM invoice_line_items WHERE invoice_id = ? ORDER BY id",
            [$id]
        );

        $patientName = $this->decryptPatientName($inv);
        $dob = EncryptionService::decrypt($inv['dob_encrypted'] ?? '');
        $phone = EncryptionService::decrypt($inv['phone_encrypted'] ?? '');

        echo json_encode([
            'status'   => 'success',
            'invoice'  => [
                'id'             => $inv['id'],
                'patient_id'     => $inv['patient_id'],
                'patient_name'   => $patientName,
                'patient_dob'    => $dob,
                'patient_phone'  => $phone,
                'encounter_id'   => $inv['encounter_id'],
                'invoice_number' => $inv['invoice_number'],
                'invoice_date'   => $inv['invoice_date'],
                'due_date'       => $inv['due_date'],
                'subtotal'       => $inv['subtotal'],
                'discount'       => $inv['discount'],
                'total_amount'   => $inv['total_amount'],
                'paid_amount'    => $inv['paid_amount'],
                'balance'        => round($inv['total_amount'] - $inv['paid_amount'], 2),
                'status'         => $inv['status'],
                'notes'          => $inv['notes'],
                'created_by'     => 'Dr. ' . $inv['created_fname'] . ' ' . $inv['created_lname'],
                'encounter_date' => $inv['encounter_date'],
                'provider_name'  => !empty($inv['prov_fname']) ? 'Dr. ' . $inv['prov_fname'] . ' ' . $inv['prov_lname'] : null,
                'created_at'     => $inv['created_at'],
            ],
            'line_items' => $lineItems,
        ]);
    }

    // PUT /api/billing/invoice/{id}/payment
    public function recordPayment(array $params): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);
        $amount = (float)($input['amount'] ?? 0);

        if (!$id || $amount <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invoice ID and payment amount required.']);
            return;
        }

        $inv = Database::fetch("SELECT * FROM invoices WHERE id = ?", [$id]);
        if (!$inv) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Invoice not found.']);
            return;
        }

        $newPaid = round((float)$inv['paid_amount'] + $amount, 2);
        $total   = (float)$inv['total_amount'];
        if ($inv['status'] === 'Paid' || $newPaid > $total + 0.005) {
            http_response_code(400);
            $due = number_format(max(0, $total - (float)$inv['paid_amount']), 2);
            echo json_encode(['status' => 'error', 'message' => $inv['status'] === 'Paid' ? 'This invoice is already fully paid.' : "Payment exceeds the balance due (\${$due})."]);
            return;
        }
        $status  = $newPaid >= $total ? 'Paid' : 'Partially Paid';

        Database::query(
            "UPDATE invoices SET paid_amount = ?, status = ? WHERE id = ?",
            [$newPaid, $status, $id]
        );

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $inv['patient_id'], "Record Payment $$amount on Invoice #{$inv['invoice_number']}", 'Billing', $id);

        echo json_encode(['status' => 'success', 'message' => "Payment of $$amount recorded. Status: $status", 'new_status' => $status, 'paid_amount' => $newPaid]);
    }

    // DELETE /api/billing/invoice/{id}
    public function deleteInvoice(array $params): void {
        $this->checkAccess(['Super Admin', 'Billing Staff']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invoice ID required.']);
            return;
        }

        Database::query("DELETE FROM invoices WHERE id = ?", [$id]);
        echo json_encode(['status' => 'success', 'message' => 'Invoice deleted.']);
    }

    // Legacy: GET /api/billing/claims
    public function index(): void {
        $this->listInvoices();
    }

    // Legacy: POST /api/billing/claims
    public function store(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Use the new invoice API.']);
    }

    // ---------------------------------------------------------------
    // GET /api/billing/queue
    // Phase 2 Billing Handoff (BILL-HANDOFF-001, BILL-HANDOFF-003)
    // Returns signed/locked encounters with billing_queue_status = 'pending_review'
    // for charge capture by billers. Core does NOT submit claims (BILL-HANDOFF-002).
    // ---------------------------------------------------------------
    public function billingQueue(): void {
        $this->checkAccess(['Super Admin', 'Billing Staff', 'Doctor']);
        header('Content-Type: application/json');

        $sql = "SELECT
                    cn.id               AS encounter_id,
                    cn.patient_id,
                    cn.provider_id,
                    cn.note_date        AS encounter_date,
                    cn.encounter_type   AS specialty,
                    cn.visit_type,
                    cn.encounter_mode,
                    cn.encounter_status,
                    cn.billing_queue_status,
                    cn.chief_complaint,
                    cn.icd10_codes,
                    cn.signed_by_name,
                    cn.signed_by_credentials,
                    cn.signed_at,
                    cn.locked_at,
                    p.first_name_encrypted,
                    p.last_name_encrypted,
                    u.first_name        AS prov_first_name,
                    u.last_name         AS prov_last_name
                FROM clinical_notes cn
                JOIN patients p ON cn.patient_id = p.id
                JOIN users u    ON cn.provider_id = u.id
                WHERE cn.lock_state = 1
                  AND cn.billing_queue_status = 'pending_review'
                ORDER BY cn.locked_at DESC";

        $rows = Database::fetchAll($sql, []);
        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'encounter_id'        => $r['encounter_id'],
                'patient_id'          => $r['patient_id'],
                'patient_name'        => $this->decryptPatientName($r),
                'provider'            => 'Dr. ' . $r['prov_first_name'] . ' ' . $r['prov_last_name'],
                'encounter_date'      => $r['encounter_date'],
                'specialty'           => $r['specialty'],
                'visit_type'          => $r['visit_type'],
                'encounter_mode'      => $r['encounter_mode'],
                'chief_complaint'     => $r['chief_complaint'],
                'icd10_codes'         => $r['icd10_codes'],
                'signed_by'           => $r['signed_by_name'] . ($r['signed_by_credentials'] ? ', ' . $r['signed_by_credentials'] : ''),
                'signed_at'           => $r['signed_at'],
                'locked_at'           => $r['locked_at'],
                'billing_queue_status'=> $r['billing_queue_status'],
            ];
        }

        AuditLogger::log(
            $_SESSION['user_id'] ?? null,
            $_SESSION['username'] ?? '',
            $_SESSION['user_role'] ?? '',
            null,
            'View Billing Queue',
            'Billing',
            null
        );

        echo json_encode(['status' => 'success', 'data' => $result, 'count' => count($result)]);
    }
}
