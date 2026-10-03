<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\EncryptionService;
use App\Services\AuditLogger;

class PatientController {
    private function checkAccess(array $allowedRoles): void {
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    // Upserts one insurance row for a patient, keyed by (patient_id, insurance_type).
    private function upsertInsurance(int $patientId, string $type, array $d): void {
        $existing = Database::fetch("SELECT id FROM patient_insurance WHERE patient_id = ? AND insurance_type = ?", [$patientId, $type]);
        $params = [
            $d['provider'] ?? '', $d['member_id'] ?? '', $d['payer_id'] ?? '', $d['policy_no'] ?? '', $d['group_no'] ?? '',
            $d['plan_name'] ?? '', $d['effective_date'] ?? null, $d['subscriber_name'] ?? '', $d['subscriber_first_name'] ?? '',
            $d['subscriber_last_name'] ?? '', $d['subscriber_dob'] ?? null, $d['subscriber_relationship'] ?? '',
            $d['subscriber_employer'] ?? '', $d['copay'] ?? null, $d['insurance_phone'] ?? null,
        ];
        if ($existing) {
            Database::query(
                "UPDATE patient_insurance SET primary_provider = ?, member_id = ?, payer_id = ?, primary_policy_no = ?, primary_group_no = ?,
                    plan_name = ?, effective_date = ?, subscriber_name = ?, subscriber_first_name = ?, subscriber_last_name = ?,
                    subscriber_dob = ?, subscriber_relationship = ?, subscriber_employer = ?, copay = ?, insurance_phone = ?
                 WHERE patient_id = ? AND insurance_type = ?",
                array_merge($params, [$patientId, $type])
            );
        } else {
            Database::query(
                "INSERT INTO patient_insurance (
                    primary_provider, member_id, payer_id, primary_policy_no, primary_group_no, plan_name, effective_date,
                    subscriber_name, subscriber_first_name, subscriber_last_name, subscriber_dob, subscriber_relationship,
                    subscriber_employer, copay, insurance_phone, patient_id, insurance_type
                 ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array_merge($params, [$patientId, $type])
            );
        }
    }

    // Decodes the repeatable emergency-contacts list, falling back to the legacy single-contact
    // columns for patients registered before the repeatable list existed.
    private function decodeEmergencyContacts(array $p): array {
        if (!empty($p['emergency_contacts_json'])) {
            $decoded = json_decode($p['emergency_contacts_json'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }
        if (!empty($p['emergency_contact_name'])) {
            $nameParts = explode(' ', trim($p['emergency_contact_name']), 2);
            return [[
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'relationship' => $p['emergency_relationship'] ?? '',
                'mobile_phone' => $p['emergency_phone'] ?? '',
                'alt_phone' => '',
                'email' => '',
            ]];
        }
        return [];
    }

    // Deterministic blind-index key for duplicate-patient detection: encrypted name/DOB
    // columns use a random IV per write, so they can never be matched with a WHERE clause.
    private function normalizeMatchKey(string $first, string $last, string $dob): string {
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', "$first|$last|$dob")));
        return EncryptionService::blindIndex($normalized);
    }

    public function providers(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $sql = "SELECT id, first_name, last_name, role, specialty FROM users WHERE role IN ('Doctor', 'Super Admin') ORDER BY first_name ASC";
        $providers = Database::fetchAll($sql);
        echo json_encode(['status' => 'success', 'data' => $providers]);
    }

    public function show(array $params): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $sql = "SELECT p.*,
                       u.first_name as provider_first_name,
                       u.last_name as provider_last_name,
                       fac.facility_name,
                       pi.primary_provider as ins_provider,
                       pi.member_id as ins_member_id,
                       pi.primary_policy_no as ins_policy,
                       pi.primary_group_no as ins_group,
                       pi.subscriber_first_name as ins_subscriber_first_name,
                       pi.subscriber_last_name as ins_subscriber_last_name,
                       pi.subscriber_employer as ins_subscriber_employer,
                       pi.insurance_phone as ins_phone,
                       pi2.primary_provider as sec_provider,
                       pi2.member_id as sec_member_id,
                       pi2.primary_policy_no as sec_policy,
                       pi2.primary_group_no as sec_group,
                       pi2.plan_name as sec_plan_name,
                       pi2.payer_id as sec_payer_id,
                       pi2.effective_date as sec_effective_date,
                       pi2.subscriber_first_name as sec_subscriber_first_name,
                       pi2.subscriber_last_name as sec_subscriber_last_name,
                       pi2.subscriber_dob as sec_subscriber_dob,
                       pi2.subscriber_relationship as sec_subscriber_relationship,
                       pi2.subscriber_employer as sec_subscriber_employer,
                       pi2.copay as sec_copay,
                       pi2.insurance_phone as sec_insurance_phone,
                       pif.status as intake_status
                FROM patients p
                LEFT JOIN users u ON p.primary_provider_id = u.id
                LEFT JOIN facilities fac ON p.facility_id = fac.id
                LEFT JOIN patient_insurance pi ON p.id = pi.patient_id AND pi.insurance_type = 'Primary'
                LEFT JOIN patient_insurance pi2 ON p.id = pi2.patient_id AND pi2.insurance_type = 'Secondary'
                LEFT JOIN patient_intake_forms pif ON pif.id = (SELECT MAX(id) FROM patient_intake_forms WHERE patient_id = p.id)
                WHERE p.id = ?";

        $pt = Database::fetch($sql, [$id]);
        if (!$pt) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $pt['first_name'] = !empty($pt['first_name_encrypted']) ? EncryptionService::decrypt($pt['first_name_encrypted']) : '';
        $pt['last_name'] = !empty($pt['last_name_encrypted']) ? EncryptionService::decrypt($pt['last_name_encrypted']) : '';
        $pt['dob'] = !empty($pt['dob_encrypted']) ? EncryptionService::decrypt($pt['dob_encrypted']) : '';
        $pt['ssn'] = !empty($pt['ssn_encrypted']) ? EncryptionService::decrypt($pt['ssn_encrypted']) : '';
        $pt['phone'] = !empty($pt['phone_encrypted']) ? EncryptionService::decrypt($pt['phone_encrypted']) : '';
        $pt['assigned_provider_name'] = trim(($pt['provider_first_name'] ?? '') . ' ' . ($pt['provider_last_name'] ?? ''));
        $pt['photo_url'] = $pt['photo_url'] ?? '';
        $pt['photo'] = $pt['photo_url'] ?? '';
        $pt['emergency_contacts'] = $this->decodeEmergencyContacts($pt);

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, $id, 'View Patient Record', 'Patient Directory', (string)$id);

        echo json_encode(['status' => 'success', 'data' => $pt]);
    }

    public function index(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $search = $_GET['search'] ?? '';
        $idFilter = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $sql = "SELECT p.*,
                       u.first_name as provider_first_name,
                       u.last_name as provider_last_name,
                       fac.facility_name,
                       pi.primary_provider as ins_provider,
                       pi.member_id as ins_member_id,
                       pi.primary_policy_no as ins_policy,
                       pi.primary_group_no as ins_group,
                       pi.insurance_phone as ins_phone,
                       pi.plan_name,
                       pi.payer_id,
                       pi.effective_date,
                       pi.subscriber_name,
                       pi.subscriber_first_name,
                       pi.subscriber_last_name,
                       pi.subscriber_employer,
                       pi.subscriber_dob,
                       pi.subscriber_relationship,
                       pi.copay,
                       pi2.primary_provider as sec_provider,
                       pi2.member_id as sec_member_id,
                       pi2.primary_policy_no as sec_policy,
                       pi2.primary_group_no as sec_group,
                       pi2.plan_name as sec_plan_name,
                       pi2.payer_id as sec_payer_id,
                       pi2.effective_date as sec_effective_date,
                       pi2.subscriber_first_name as sec_subscriber_first_name,
                       pi2.subscriber_last_name as sec_subscriber_last_name,
                       pi2.subscriber_dob as sec_subscriber_dob,
                       pi2.subscriber_relationship as sec_subscriber_relationship,
                       pi2.subscriber_employer as sec_subscriber_employer,
                       pi2.copay as sec_copay,
                       pi2.insurance_phone as sec_insurance_phone,
                       pif.status as intake_status,
                       (SELECT MAX(start_time) FROM appointments WHERE patient_id = p.id AND start_time < NOW() AND COALESCE(status, 'Scheduled') NOT IN ('Cancelled', 'No Show', 'Waiting List') AND COALESCE(category, '') != 'Waiting List') as last_appt,
                       (SELECT MIN(start_time) FROM appointments WHERE patient_id = p.id AND start_time >= NOW() AND COALESCE(status, 'Scheduled') NOT IN ('Cancelled', 'No Show', 'Waiting List') AND COALESCE(category, '') != 'Waiting List') as next_appt,
                       (SELECT GROUP_CONCAT(DISTINCT CONCAT(ux.first_name, ' ', ux.last_name) ORDER BY ux.last_name SEPARATOR ', ') FROM appointments ax JOIN users ux ON ux.id = ax.provider_id WHERE ax.patient_id = p.id AND COALESCE(ax.status, 'Scheduled') NOT IN ('Cancelled', 'No Show', 'Waiting List') AND COALESCE(ax.category, '') != 'Waiting List') as appt_clinicians,
                       (SELECT COUNT(*) FROM clinical_notes WHERE patient_id = p.id) as notes_count,
                       (SELECT COUNT(*) FROM patient_documents WHERE patient_id = p.id) as docs_count
                FROM patients p
                LEFT JOIN users u ON p.primary_provider_id = u.id
                LEFT JOIN facilities fac ON p.facility_id = fac.id
                LEFT JOIN patient_insurance pi ON p.id = pi.patient_id AND pi.insurance_type = 'Primary'
                LEFT JOIN patient_insurance pi2 ON p.id = pi2.patient_id AND pi2.insurance_type = 'Secondary'
                LEFT JOIN patient_intake_forms pif ON pif.id = (SELECT MAX(id) FROM patient_intake_forms WHERE patient_id = p.id)
                " . ($idFilter ? "WHERE p.id = ?" : "") . "
                ORDER BY p.id DESC";
        $params = $idFilter ? [$idFilter] : [];

        $patients = Database::fetchAll($sql, $params);

        $decryptedPatients = [];
        foreach ($patients as $p) {
            $firstName = EncryptionService::decrypt($p['first_name_encrypted']);
            $lastName = EncryptionService::decrypt($p['last_name_encrypted']);
            $dob = EncryptionService::decrypt($p['dob_encrypted']);
            $ssn = !empty($p['ssn_encrypted']) ? EncryptionService::decrypt($p['ssn_encrypted']) : '';
            $phone = !empty($p['phone_encrypted']) ? EncryptionService::decrypt($p['phone_encrypted']) : '';
            $homePhone = !empty($p['home_phone_encrypted']) ? EncryptionService::decrypt($p['home_phone_encrypted']) : '';
            $workPhone = !empty($p['work_phone_encrypted']) ? EncryptionService::decrypt($p['work_phone_encrypted']) : '';
            $address = !empty($p['address_encrypted']) ? EncryptionService::decrypt($p['address_encrypted']) : '';

            if ($search !== '') {
                $searchLower = strtolower($search);
                if (strpos(strtolower($firstName), $searchLower) === false &&
                    strpos(strtolower($lastName), $searchLower) === false &&
                    strpos(strtolower($dob), $searchLower) === false) {
                    continue;
                }
            }

            $age = $p['age'];
            if ($age === null && !empty($dob)) {
                try {
                    $birthDate = new \DateTime($dob);
                    $today = new \DateTime();
                    $age = $today->diff($birthDate)->y;
                } catch (\Exception $e) {
                    $age = null;
                }
            }

            $assignedProviderName = '';
            if (!empty($p['provider_first_name'])) {
                $assignedProviderName = 'Dr. ' . $p['provider_first_name'] . ' ' . $p['provider_last_name'];
            }

            $lastApptFormatted = '';
            if (!empty($p['last_appt'])) {
                try {
                    $d = new \DateTime($p['last_appt']);
                    $lastApptFormatted = $d->format('n/j/Y');
                } catch (\Exception $e) { $lastApptFormatted = ''; }
            }

            $nextApptFormatted = '';
            if (!empty($p['next_appt'])) {
                try {
                    $d = new \DateTime($p['next_appt']);
                    $nextApptFormatted = $d->format('n/j/Y g:i A');
                } catch (\Exception $e) { $nextApptFormatted = ''; }
            }

            $clinicians = [];
            if (!empty($p['provider_first_name'])) {
                $clinicians[] = trim($p['provider_first_name'] . ' ' . $p['provider_last_name']);
            }
            // Clinicians = assigned provider plus every provider this patient actually has appointments with
            if (!empty($p['appt_clinicians'])) {
                foreach (explode(', ', $p['appt_clinicians']) as $apptClinician) {
                    $clinicians[] = trim($apptClinician);
                }
            }
            $cliniciansStr = implode(', ', array_unique(array_filter($clinicians)));
            if (empty($cliniciansStr) && !empty($assignedProviderName)) {
                $cliniciansStr = $assignedProviderName;
            }

            $payer = !empty($p['ins_provider']) ? $p['ins_provider'] : (!empty($p['payer_id']) ? $p['payer_id'] : (!empty($p['plan_name']) ? $p['plan_name'] : ''));
            $docCount = (int)($p['notes_count'] ?? 0) + (int)($p['docs_count'] ?? 0);

            $decryptedPatients[] = [
                'id' => $p['id'],
                'first_name' => $firstName,
                'middle_name' => $p['middle_name'] ?? '',
                'last_name' => $lastName,
                'dob' => $dob,
                'age' => $age,
                'gender' => $p['gender'] ?? '',
                'marital_status' => $p['marital_status'] ?? '',
                'ssn' => $ssn,
                'language' => $p['language'] ?? '',
                'race' => $p['race'] ?? '',
                'ethnicity' => $p['ethnicity'] ?? '',
                'smoking_status' => $p['smoking_status'] ?? '',
                'employment_status' => $p['employment_status'] ?? '',
                'sexual_orientation' => $p['sexual_orientation'] ?? '',
                'phone' => $phone,
                'home_phone' => $homePhone,
                'work_phone' => $workPhone,
                'work_phone_ext' => $p['work_phone_ext'] ?? '',
                'email' => $p['email'] ?? '',
                'address' => $address,
                'address_line2' => $p['address_line2'] ?? '',
                'city' => $p['city'] ?? '',
                'state' => $p['state'] ?? '',
                'country' => $p['country'] ?? 'United States',
                'zip' => $p['zip'] ?? '',
                'is_po_box' => $p['is_po_box'] ?? 0,
                'county' => $p['county'] ?? '',
                'gender_identity' => $p['gender_identity'] ?? '',
                'pronouns' => $p['pronouns'] ?? '',
                'nickname' => $p['nickname'] ?? '',
                'suffix' => $p['suffix'] ?? '',
                'maiden_name' => $p['maiden_name'] ?? '',
                'previous_name' => $p['previous_name'] ?? '',
                'category' => $p['category'] ?? '',
                'payment_source' => $p['payment_source'] ?? '',
                'blood_group' => $p['blood_group'] ?? '',
                'patient_ids_json' => !empty($p['patient_ids_json']) ? json_decode($p['patient_ids_json'], true) : [],
                'previous_address_json' => !empty($p['previous_address_json']) ? json_decode($p['previous_address_json'], true) : [],
                'caregivers_json' => !empty($p['caregivers_json']) ? json_decode($p['caregivers_json'], true) : [],
                'guarantor_json' => !empty($p['guarantor_json']) ? json_decode($p['guarantor_json'], true) : [],
                'about_patient' => $p['about_patient'] ?? '',
                'hear_source' => $p['hear_source'] ?? '',
                'hear_specific_source' => $p['hear_specific_source'] ?? '',
                'mother_maiden_first_name' => $p['mother_maiden_first_name'] ?? '',
                'mother_maiden_last_name' => $p['mother_maiden_last_name'] ?? '',
                'multiple_birth' => $p['multiple_birth'] ?? 0,
                'phr_invitation' => $p['phr_invitation'] ?? 'To Patient',
                'preferred_communication' => $p['preferred_communication'] ?? '',
                'email_notifications' => $p['email_notifications'] ?? 1,
                'text_notifications' => $p['text_notifications'] ?? 1,
                'voice_notifications' => $p['voice_notifications'] ?? 1,
                'sexual_orientation_declined' => $p['sexual_orientation_declined'] ?? 0,
                'gender_identity_declined' => $p['gender_identity_declined'] ?? 0,
                'language_declined' => $p['language_declined'] ?? 0,
                'race_declined' => $p['race_declined'] ?? 0,
                'ethnicity_declined' => $p['ethnicity_declined'] ?? 0,
                'emergency_contact_name' => $p['emergency_contact_name'] ?? '',
                'emergency_relationship' => $p['emergency_relationship'] ?? '',
                'emergency_phone' => $p['emergency_phone'] ?? '',
                'emergency_phone_ext' => $p['emergency_phone_ext'] ?? '',
                'emergency_contacts' => $this->decodeEmergencyContacts($p),
                'created_at' => $p['created_at'],
                'primary_provider_id' => $p['primary_provider_id'],
                'assigned_provider_name' => $assignedProviderName,
                'facility_id' => $p['facility_id'] ?? null,
                'facility_name' => $p['facility_name'] ?? '',
                'patient_status' => $p['patient_status'] ?? 'Active',
                'communication_consent' => $p['communication_consent'] ?? 0,
                'insurance_provider' => $p['ins_provider'] ?? '',
                'member_id' => $p['ins_member_id'] ?? '',
                'payer_id' => $p['payer_id'] ?? '',
                'insurance_policy' => $p['ins_policy'] ?? '',
                'policy_number' => $p['ins_policy'] ?? '',
                'group_number' => $p['ins_group'] ?? '',
                'plan_name' => $p['plan_name'] ?? '',
                'insurance_plan_name' => $p['plan_name'] ?? '',
                'effective_date' => $p['effective_date'] ?? '',
                'insurance_effective_date' => $p['effective_date'] ?? '',
                'subscriber_name' => $p['subscriber_name'] ?? '',
                'subscriber_first_name' => $p['subscriber_first_name'] ?? '',
                'subscriber_last_name' => $p['subscriber_last_name'] ?? '',
                'subscriber_employer' => $p['subscriber_employer'] ?? '',
                'subscriber_dob' => $p['subscriber_dob'] ?? '',
                'subscriber_relationship' => $p['subscriber_relationship'] ?? 'Self',
                'copay_amount' => $p['copay'] ?? '',
                'copay' => $p['copay'] ?? '',
                'insurance_phone' => $p['ins_phone'] ?? '',
                'secondary_insurance_provider' => $p['sec_provider'] ?? '',
                'secondary_member_id' => $p['sec_member_id'] ?? '',
                'secondary_policy_number' => $p['sec_policy'] ?? '',
                'secondary_group_number' => $p['sec_group'] ?? '',
                'secondary_plan_name' => $p['sec_plan_name'] ?? '',
                'secondary_payer_id' => $p['sec_payer_id'] ?? '',
                'secondary_effective_date' => $p['sec_effective_date'] ?? '',
                'secondary_subscriber_first_name' => $p['sec_subscriber_first_name'] ?? '',
                'secondary_subscriber_last_name' => $p['sec_subscriber_last_name'] ?? '',
                'secondary_subscriber_dob' => $p['sec_subscriber_dob'] ?? '',
                'secondary_subscriber_relationship' => $p['sec_subscriber_relationship'] ?? '',
                'secondary_subscriber_employer' => $p['sec_subscriber_employer'] ?? '',
                'secondary_copay' => $p['sec_copay'] ?? '',
                'secondary_insurance_phone' => $p['sec_insurance_phone'] ?? '',
                'intake_status' => $p['intake_status'] ?? '',
                'photo_url' => $p['photo_url'] ?? '',
                'photo' => $p['photo_url'] ?? '',
                'last_appt' => $lastApptFormatted,
                'next_appt' => $nextApptFormatted,
                'payer' => $payer,
                'clinicians' => $cliniciansStr,
                'doc_count' => $docCount
            ];
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'View Patient List', 'Patient Directory');

        echo json_encode(['status' => 'success', 'data' => $decryptedPatients]);
    }

    /**
     * Lean list for the Patient Directory table: only what the table (and its export) needs, with no insurance /
     * intake / facility joins. The full record for a chart or the edit wizard is fetched per patient via
     * GET /api/patients?id=N (same shape as the full list).
     */
    public function listView(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        // Real appointments only: Cancelled, No Show and Waiting List placeholders never count
        $real = "COALESCE(status, 'Scheduled') NOT IN ('Cancelled', 'No Show', 'Waiting List') AND COALESCE(category, '') != 'Waiting List'";
        $realAx = "COALESCE(ax.status, 'Scheduled') NOT IN ('Cancelled', 'No Show', 'Waiting List') AND COALESCE(ax.category, '') != 'Waiting List'";
        $sql = "SELECT p.id, p.first_name_encrypted, p.last_name_encrypted, p.dob_encrypted, p.phone_encrypted,
                       p.home_phone_encrypted, p.ssn_encrypted, p.email, p.patient_status, p.primary_provider_id,
                       u.first_name AS provider_first_name, u.last_name AS provider_last_name,
                       (SELECT MAX(start_time) FROM appointments WHERE patient_id = p.id AND start_time < NOW() AND {$real}) AS last_appt_raw,
                       (SELECT MIN(start_time) FROM appointments WHERE patient_id = p.id AND start_time >= NOW() AND {$real}) AS next_appt_raw,
                       (SELECT MIN(start_time) FROM appointments WHERE patient_id = p.id AND DATE(start_time) = CURDATE() AND {$real}) AS today_appt_raw,
                       (SELECT GROUP_CONCAT(DISTINCT CONCAT(ux.first_name, ' ', ux.last_name) ORDER BY ux.last_name SEPARATOR ', ') FROM appointments ax JOIN users ux ON ux.id = ax.provider_id WHERE ax.patient_id = p.id AND {$realAx}) AS appt_clinicians,
                       (SELECT COUNT(*) FROM clinical_notes WHERE patient_id = p.id) AS notes_count,
                       (SELECT COUNT(*) FROM patient_documents WHERE patient_id = p.id) AS docs_count
                FROM patients p
                LEFT JOIN users u ON p.primary_provider_id = u.id
                ORDER BY p.id DESC";

        $fmt = function ($raw, string $format): string {
            if (empty($raw)) return '';
            try { return (new \DateTime($raw))->format($format); } catch (\Exception $e) { return ''; }
        };

        $out = [];
        foreach (Database::fetchAll($sql) as $p) {
            $assigned = !empty($p['provider_first_name']) ? 'Dr. ' . $p['provider_first_name'] . ' ' . $p['provider_last_name'] : '';
            $clinicians = [];
            if (!empty($p['provider_first_name'])) {
                $clinicians[] = trim($p['provider_first_name'] . ' ' . $p['provider_last_name']);
            }
            if (!empty($p['appt_clinicians'])) {
                foreach (explode(', ', $p['appt_clinicians']) as $c) $clinicians[] = trim($c);
            }
            $out[] = [
                'id' => $p['id'],
                'first_name' => EncryptionService::decrypt($p['first_name_encrypted']),
                'last_name' => EncryptionService::decrypt($p['last_name_encrypted']),
                'dob' => EncryptionService::decrypt($p['dob_encrypted']),
                'phone' => !empty($p['phone_encrypted']) ? EncryptionService::decrypt($p['phone_encrypted']) : '',
                'home_phone' => !empty($p['home_phone_encrypted']) ? EncryptionService::decrypt($p['home_phone_encrypted']) : '',
                'ssn' => !empty($p['ssn_encrypted']) ? EncryptionService::decrypt($p['ssn_encrypted']) : '',
                'email' => $p['email'] ?? '',
                'patient_status' => $p['patient_status'] ?? 'Active',
                'primary_provider_id' => $p['primary_provider_id'],
                'assigned_provider_name' => $assigned,
                'clinicians' => implode(', ', array_unique(array_filter($clinicians))) ?: $assigned,
                'last_appt' => $fmt($p['last_appt_raw'], 'n/j/Y'),
                'next_appt' => $fmt($p['next_appt_raw'], 'n/j/Y g:i A'),
                'last_appt_raw' => $p['last_appt_raw'] ?? '',
                'next_appt_raw' => $p['next_appt_raw'] ?? '',
                'today_appt' => $fmt($p['today_appt_raw'], 'g:i A'),
                'doc_count' => (int)($p['notes_count'] ?? 0) + (int)($p['docs_count'] ?? 0),
            ];
        }

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, 'View Patient List', 'Patient Directory', null);

        echo json_encode(['status' => 'success', 'data' => $out]);
    }

    // Active <-> Inactive toggle for the directory. Deliberately tiny: the full update() demands phone/email/etc.
    public function updateStatus(array $params): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $id = intval($params['id'] ?? 0);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['patient_status'] ?? '';
        if (!$id || !in_array($status, ['Active', 'Inactive'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'A valid patient and a status of Active or Inactive are required.']);
            return;
        }

        $row = Database::fetch("SELECT id, patient_status FROM patients WHERE id = ?", [$id]);
        if (!$row) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }
        if (!in_array($row['patient_status'], ['Active', 'Inactive'], true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Deceased or merged records cannot be changed here.']);
            return;
        }

        Database::query("UPDATE patients SET patient_status = ? WHERE id = ?", [$status, $id]);
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $id, 'Set Patient Status to ' . $status, 'Patient Directory', $id);

        echo json_encode(['status' => 'success', 'message' => 'Patient marked ' . $status . '.', 'patient_status' => $status]);
    }

    /**
     * Format checks for the fields the registration wizard / quick popup collect. Only keys that are actually
     * present (and non-empty) are checked, so partial updates from other screens are unaffected.
     * Returns an error message, or null when everything supplied is acceptable.
     */
    private function validatePatientInput(?array $in): ?string {
        $in = $in ?? [];
        $dob = trim((string)($in['dob'] ?? ''));
        if ($dob !== '') {
            $d = \DateTime::createFromFormat('Y-m-d', $dob);
            if (!$d || $d->format('Y-m-d') !== $dob) return 'Date of birth is not a valid date.';
            if ($d > new \DateTime('today')) return 'Date of birth cannot be in the future.';
            if ($d < new \DateTime('1900-01-01')) return 'Date of birth is not valid.';
        }
        $email = trim((string)($in['email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Email address is not valid.';
        foreach (['phone' => 'Mobile phone', 'home_phone' => 'Home phone', 'emergency_phone' => 'Emergency phone'] as $key => $label) {
            $v = trim((string)($in[$key] ?? ''));
            if ($v === '') continue;
            $digits = strlen(preg_replace('/\D/', '', $v));
            if ($digits < 7 || $digits > 15) return "{$label} number is not valid.";
        }
        $zip = trim((string)($in['zip'] ?? ''));
        $country = trim((string)($in['country'] ?? 'United States'));
        if ($zip !== '' && ($country === '' || $country === 'United States') && !preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
            return 'Zip code must be 5 digits (or ZIP+4).';
        }
        return null;
    }

    // The wizard sends one emergency contact as emergency_contact_name/relationship/phone; the tables store a list.
    private function legacyEmergencyToList(array $in): array {
        $name = trim((string)($in['emergency_contact_name'] ?? ''));
        if ($name === '') return [];
        $parts = preg_split('/\s+/', $name, 2);
        return [[
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '',
            'relationship' => $in['emergency_relationship'] ?? '',
            'mobile_phone' => $in['emergency_phone'] ?? '',
        ]];
    }

    public function store(): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $firstName = $input['first_name'] ?? '';
        $middleName = $input['middle_name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        $dob = $input['dob'] ?? '';
        $ssn = $input['ssn'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $homePhone = $input['home_phone'] ?? '';
        $workPhone = $input['work_phone'] ?? '';
        $workPhoneExt = $input['work_phone_ext'] ?? '';
        $address = $input['address'] ?? '';
        $addressLine2 = $input['address_line2'] ?? '';
        $city = $input['city'] ?? '';
        $state = $input['state'] ?? '';
        $country = $input['country'] ?? 'United States';
        $zip = $input['zip'] ?? '';
        $isPoBox = !empty($input['is_po_box']) ? 1 : 0;
        $county = $input['county'] ?? '';
        $primaryProviderId = !empty($input['primary_provider_id']) ? intval($input['primary_provider_id']) : null;
        $insuranceProvider = $input['insurance_provider'] ?? '';
        $insurancePolicy = $input['insurance_policy'] ?? '';
        $facilityId = !empty($input['facility_id']) ? intval($input['facility_id']) : null;
        $isDraft = !empty($input['is_draft']);
        $patientStatus = $isDraft ? 'Draft' : ($input['patient_status'] ?? 'Active');
        $communicationConsent = !empty($input['communication_consent']) ? 1 : 0;
        $emergencyContacts = is_array($input['emergency_contacts_json'] ?? null) ? $input['emergency_contacts_json'] : [];
        if (empty($emergencyContacts) && is_array($input)) {
            $emergencyContacts = $this->legacyEmergencyToList($input);
        }
        $emergencyContactsJson = !empty($emergencyContacts) ? json_encode($emergencyContacts) : null;

        $gender = $input['gender'] ?? '';
        $genderIdentity = $input['gender_identity'] ?? '';
        $pronouns = $input['pronouns'] ?? '';
        $nickname = $input['nickname'] ?? '';
        $suffix = $input['suffix'] ?? '';
        $maidenName = $input['maiden_name'] ?? '';
        $previousName = $input['previous_name'] ?? '';
        $linkedPatient = $input['linked_patient'] ?? '';
        $patientIdsJson = is_array($input['patient_ids_json'] ?? null) ? json_encode($input['patient_ids_json']) : ($input['patient_ids_json'] ?? null);

        $previousAddressJson = is_array($input['previous_address_json'] ?? null) ? json_encode($input['previous_address_json']) : ($input['previous_address_json'] ?? null);
        $emergencyContactName = $input['emergency_contact_name'] ?? '';
        $emergencyPhone = $input['emergency_phone'] ?? '';
        $emergencyPhoneExt = $input['emergency_phone_ext'] ?? '';
        $caregiversJson = is_array($input['caregivers_json'] ?? null) ? json_encode($input['caregivers_json']) : ($input['caregivers_json'] ?? null);
        $guarantorJson = is_array($input['guarantor_json'] ?? null) ? json_encode($input['guarantor_json']) : ($input['guarantor_json'] ?? null);

        $preferredCommunication = $input['preferred_communication'] ?? '';
        $emailNotifications = isset($input['email_notifications']) ? ($input['email_notifications'] ? 1 : 0) : 1;
        $textNotifications = isset($input['text_notifications']) ? ($input['text_notifications'] ? 1 : 0) : 1;
        $voiceNotifications = isset($input['voice_notifications']) ? ($input['voice_notifications'] ? 1 : 0) : 1;

        $phrInvitation = $input['phr_invitation'] ?? 'To Patient';
        $category = $input['category'] ?? '';
        $paymentSource = $input['payment_source'] ?? '';
        $bloodGroup = $input['blood_group'] ?? '';
        $language = $input['language'] ?? '';
        $race = $input['race'] ?? '';
        $ethnicity = $input['ethnicity'] ?? '';
        $smokingStatus = $input['smoking_status'] ?? '';
        $maritalStatus = $input['marital_status'] ?? '';
        $employmentStatus = $input['employment_status'] ?? '';
        $sexualOrientation = $input['sexual_orientation'] ?? '';
        $sexualOrientationDeclined = !empty($input['sexual_orientation_declined']) ? 1 : 0;
        $genderIdentityDeclined = !empty($input['gender_identity_declined']) ? 1 : 0;
        $languageDeclined = !empty($input['language_declined']) ? 1 : 0;
        $raceDeclined = !empty($input['race_declined']) ? 1 : 0;
        $ethnicityDeclined = !empty($input['ethnicity_declined']) ? 1 : 0;
        $motherMaidenFirstName = $input['mother_maiden_first_name'] ?? '';
        $motherMaidenLastName = $input['mother_maiden_last_name'] ?? '';
        $multipleBirth = !empty($input['multiple_birth']) ? 1 : 0;
        $aboutPatient = $input['about_patient'] ?? '';
        $hearSource = $input['hear_source'] ?? '';
        $hearSpecificSource = $input['hear_specific_source'] ?? '';
        $photoUrl = $input['photo_url'] ?? '';

        // MVP quick-registration: only name, DOB, phone, and email are required at intake.
        // Everything else (address, facility, emergency contacts, insurance) can be added later via Edit.
        // A draft (wizard "Save Draft") only needs a name; it is stored with status 'Draft' and finished later.
        if ($isDraft) {
            if (empty($firstName) && empty($lastName)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'A draft needs at least a first or last name.']);
                return;
            }
        } elseif (empty($firstName) || empty($lastName) || empty($dob)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'First name, last name, and date of birth are required.']);
            return;
        }
        if (!$isDraft && ($validationError = $this->validatePatientInput($input))) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $validationError]);
            return;
        }
        // The scheduling popup's quick "New Patient" flow sets allow_incomplete_contact so front desk can
        // book someone who hasn't given a phone/email yet; every other caller still requires both.
        $allowIncompleteContact = !empty($input['allow_incomplete_contact']) || $isDraft;
        if (empty($phone) && !$allowIncompleteContact) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Mobile phone is required.']);
            return;
        }
        if (empty($email)) {
            if (!$allowIncompleteContact) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
                return;
            }
            $email = null;
        }
        $primaryMemberId = $input['member_id'] ?? '';
        $primarySubFirst = $input['subscriber_first_name'] ?? '';
        $primarySubLast = $input['subscriber_last_name'] ?? '';
        $primarySubDob = $input['subscriber_dob'] ?? '';
        $primarySubRel = $input['subscriber_relationship'] ?? '';

        $age = null;
        if (!empty($dob)) {
            try {
                $birthDate = new \DateTime($dob);
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
            } catch (\Exception $e) {
                $age = null;
            }
        }

        $firstEnc = EncryptionService::encrypt($firstName);
        $lastEnc = EncryptionService::encrypt($lastName);
        $dobEnc = EncryptionService::encrypt($dob);
        $ssnEnc = $ssn ? EncryptionService::encrypt($ssn) : null;
        $phoneEnc = $phone ? EncryptionService::encrypt($phone) : null;
        $homePhoneEnc = $homePhone ? EncryptionService::encrypt($homePhone) : null;
        $workPhoneEnc = $workPhone ? EncryptionService::encrypt($workPhone) : null;
        $addressEnc = $address ? EncryptionService::encrypt($address) : null;
        $matchHash = $isDraft ? null : $this->normalizeMatchKey($firstName, $lastName, $dob);

        if (!$isDraft && empty($input['confirm_duplicate'])) {
            $existingMatch = Database::fetch("SELECT id FROM patients WHERE patient_match_hash = ?", [$matchHash]);
            if ($existingMatch) {
                echo json_encode([
                    'status' => 'duplicate_warning',
                    'message' => "A patient named {$firstName} {$lastName} (DOB {$dob}) already exists.",
                    'existing_patient_id' => $existingMatch['id'],
                ]);
                return;
            }
        }

        // Dual-write the first emergency contact into the legacy singular columns for
        // backward compat with any other reader that hasn't moved to emergency_contacts_json.
        $firstContact = $emergencyContacts[0] ?? [];
        $emergencyContactName = trim(($firstContact['first_name'] ?? '') . ' ' . ($firstContact['last_name'] ?? ''));
        $emergencyRelationship = $firstContact['relationship'] ?? '';
        $emergencyPhone = $firstContact['mobile_phone'] ?? '';
        $emergencyPhoneExt = '';

        $sql = "INSERT INTO patients (
            first_name_encrypted, middle_name, last_name_encrypted, dob_encrypted, patient_match_hash, gender, age, ssn_encrypted, email,
            phone_encrypted, home_phone_encrypted, work_phone_encrypted, work_phone_ext, address_encrypted,
            address_line2, city, state, country, zip, is_po_box, county, primary_provider_id,
            gender_identity, pronouns, nickname, suffix, maiden_name, previous_name, linked_patient, patient_ids_json,
            previous_address_json, emergency_contact_name, emergency_relationship, emergency_phone, emergency_phone_ext,
            emergency_contacts_json, caregivers_json, guarantor_json, preferred_communication,
            email_notifications, text_notifications, voice_notifications, phr_invitation, category, payment_source,
            blood_group, language, race, ethnicity, smoking_status, marital_status, employment_status, sexual_orientation,
            sexual_orientation_declined, mother_maiden_first_name, mother_maiden_last_name, multiple_birth, about_patient,
            hear_source, hear_specific_source, photo_url, facility_id, patient_status, communication_consent,
            gender_identity_declined, language_declined, race_declined, ethnicity_declined
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?
        )";

        Database::query($sql, [
            $firstEnc, $middleName, $lastEnc, $dobEnc, $matchHash, $gender, $age, $ssnEnc, $email,
            $phoneEnc, $homePhoneEnc, $workPhoneEnc, $workPhoneExt, $addressEnc,
            $addressLine2, $city, $state, $country, $zip, $isPoBox, $county, $primaryProviderId,
            $genderIdentity, $pronouns, $nickname, $suffix, $maidenName, $previousName, $linkedPatient, $patientIdsJson,
            $previousAddressJson, $emergencyContactName, $emergencyRelationship, $emergencyPhone, $emergencyPhoneExt,
            $emergencyContactsJson, $caregiversJson, $guarantorJson, $preferredCommunication,
            $emailNotifications, $textNotifications, $voiceNotifications, $phrInvitation, $category, $paymentSource,
            $bloodGroup, $language, $race, $ethnicity, $smokingStatus, $maritalStatus, $employmentStatus, $sexualOrientation,
            $sexualOrientationDeclined, $motherMaidenFirstName, $motherMaidenLastName, $multipleBirth, $aboutPatient,
            $hearSource, $hearSpecificSource, $photoUrl, $facilityId, $patientStatus, $communicationConsent,
            $genderIdentityDeclined, $languageDeclined, $raceDeclined, $ethnicityDeclined
        ]);
        $newId = Database::lastInsertId();

        if (!empty($insuranceProvider) || !empty($primaryMemberId) || !empty($insurancePolicy)) {
            $this->upsertInsurance($newId, 'Primary', [
                'provider' => $insuranceProvider, 'member_id' => $primaryMemberId, 'payer_id' => $input['payer_id'] ?? '',
                'policy_no' => $insurancePolicy, 'group_no' => $input['group_number'] ?? '', 'plan_name' => $input['plan_name'] ?? '',
                'effective_date' => !empty($input['effective_date']) ? $input['effective_date'] : null,
                'subscriber_name' => trim($primarySubFirst . ' ' . $primarySubLast), 'subscriber_first_name' => $primarySubFirst,
                'subscriber_last_name' => $primarySubLast, 'subscriber_dob' => $primarySubDob ?: null, 'subscriber_relationship' => $primarySubRel,
                'subscriber_employer' => $input['subscriber_employer'] ?? '', 'copay' => $input['copay'] ?? ($input['copay_amount'] ?? null),
                'insurance_phone' => $input['insurance_phone'] ?? null,
            ]);
        }

        $secondary = is_array($input['secondary_insurance'] ?? null) ? $input['secondary_insurance'] : [];
        if (!empty($secondary['provider']) || !empty($secondary['policy_no'])) {
            $secSubFirst = $secondary['subscriber_first_name'] ?? '';
            $secSubLast = $secondary['subscriber_last_name'] ?? '';
            $this->upsertInsurance($newId, 'Secondary', [
                'provider' => $secondary['provider'] ?? '', 'member_id' => $secondary['member_id'] ?? '',
                'payer_id' => $secondary['payer_id'] ?? '', 'policy_no' => $secondary['policy_no'] ?? '',
                'group_no' => $secondary['group_no'] ?? '', 'plan_name' => $secondary['plan_name'] ?? '',
                'effective_date' => !empty($secondary['effective_date']) ? $secondary['effective_date'] : null,
                'subscriber_name' => trim($secSubFirst . ' ' . $secSubLast), 'subscriber_first_name' => $secSubFirst,
                'subscriber_last_name' => $secSubLast, 'subscriber_dob' => !empty($secondary['subscriber_dob']) ? $secondary['subscriber_dob'] : null,
                'subscriber_relationship' => $secondary['subscriber_relationship'] ?? '', 'subscriber_employer' => $secondary['subscriber_employer'] ?? '',
                'copay' => $secondary['copay'] ?? null, 'insurance_phone' => $secondary['insurance_phone'] ?? null,
            ]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $newId, 'Create Patient Record', 'Patient Directory', $newId);

        echo json_encode(['status' => 'success', 'message' => 'Patient created successfully.', 'id' => $newId]);
    }

    public function update(array $params): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');
        
        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is missing.']);
            return;
        }

        $existingPatient = Database::fetch("SELECT * FROM patients WHERE id = ?", [$id]);
        if (!$existingPatient) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient record not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $firstName = !empty($input['first_name']) ? $input['first_name'] : EncryptionService::decrypt($existingPatient['first_name_encrypted']);
        $lastName = !empty($input['last_name']) ? $input['last_name'] : EncryptionService::decrypt($existingPatient['last_name_encrypted']);
        $dob = !empty($input['dob']) ? $input['dob'] : EncryptionService::decrypt($existingPatient['dob_encrypted']);
        $email = isset($input['email']) ? $input['email'] : ($existingPatient['email'] ?? '');
        $phone = isset($input['phone']) ? $input['phone'] : (!empty($existingPatient['phone_encrypted']) ? EncryptionService::decrypt($existingPatient['phone_encrypted']) : '');
        $address = isset($input['address']) ? $input['address'] : (!empty($existingPatient['address_encrypted']) ? EncryptionService::decrypt($existingPatient['address_encrypted']) : '');
        $primaryProviderId = !empty($input['primary_provider_id']) ? intval($input['primary_provider_id']) : $existingPatient['primary_provider_id'];
        $insuranceProvider = $input['insurance_provider'] ?? ($input['primary_provider'] ?? '');
        $insurancePolicy = $input['insurance_policy'] ?? ($input['policy_number'] ?? '');
        $city = isset($input['city']) ? $input['city'] : ($existingPatient['city'] ?? '');
        $state = isset($input['state']) ? $input['state'] : ($existingPatient['state'] ?? '');
        $country = isset($input['country']) ? $input['country'] : ($existingPatient['country'] ?? 'United States');
        $zip = isset($input['zip']) ? $input['zip'] : ($existingPatient['zip'] ?? '');
        $facilityId = isset($input['facility_id']) ? (!empty($input['facility_id']) ? intval($input['facility_id']) : null) : $existingPatient['facility_id'];
        $patientStatus = $input['patient_status'] ?? ($existingPatient['patient_status'] ?? 'Active');
        $communicationConsent = isset($input['communication_consent']) ? ($input['communication_consent'] ? 1 : 0) : (int)($existingPatient['communication_consent'] ?? 0);
        $emergencyContacts = is_array($input['emergency_contacts_json'] ?? null)
            ? $input['emergency_contacts_json']
            : (!empty($existingPatient['emergency_contacts_json']) ? (json_decode($existingPatient['emergency_contacts_json'], true) ?: []) : []);
        // The wizard sends its single emergency contact under the legacy keys; convert to the stored list.
        if (is_array($input) && !is_array($input['emergency_contacts_json'] ?? null) && array_key_exists('emergency_contact_name', $input)) {
            $emergencyContacts = $this->legacyEmergencyToList($input);
        }
        $emergencyContactsJson = !empty($emergencyContacts) ? json_encode($emergencyContacts) : null;

        // Drafts stay 'Draft' while saved as drafts; finishing one (a normal save) makes it Active.
        $isDraft = is_array($input) && !empty($input['is_draft']);
        if ($isDraft) {
            $patientStatus = 'Draft';
        } elseif (($existingPatient['patient_status'] ?? '') === 'Draft' && $patientStatus === 'Draft') {
            $patientStatus = 'Active';
        }

        // MVP quick-registration: only name, DOB, phone, and email are required.
        // Everything else (address, facility, emergency contacts, insurance) can be added later via Edit.
        if (!$isDraft) {
            if (empty($firstName) || empty($lastName) || empty($dob)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'First name, last name, and DOB are required.']);
                return;
            }
            if (empty($phone)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Mobile phone is required.']);
                return;
            }
            if (empty($email)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
                return;
            }
            if ($validationError = $this->validatePatientInput($input)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $validationError]);
                return;
            }
        }
        $primaryMemberId = $input['member_id'] ?? '';
        $primarySubFirst = $input['subscriber_first_name'] ?? '';
        $primarySubLast = $input['subscriber_last_name'] ?? '';
        $primarySubDob = $input['subscriber_dob'] ?? '';
        $primarySubRel = $input['subscriber_relationship'] ?? '';

        $age = null;
        if (!empty($dob)) {
            try {
                $birthDate = new \DateTime($dob);
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
            } catch (\Exception $e) {
                $age = null;
            }
        }

        $firstEnc = EncryptionService::encrypt($firstName);
        $lastEnc = EncryptionService::encrypt($lastName);
        $dobEnc = EncryptionService::encrypt($dob);
        $matchHash = $isDraft ? null : $this->normalizeMatchKey($firstName, $lastName, $dob);
        $ssnEnc = !empty($input['ssn']) ? EncryptionService::encrypt($input['ssn']) : null;
        $phoneEnc = $phone ? EncryptionService::encrypt($phone) : null;
        $addressEnc = $address ? EncryptionService::encrypt($address) : null;

        $middleName = $input['middle_name'] ?? '';
        $gender = $input['gender'] ?? '';
        $marital = $input['marital_status'] ?? '';
        $language = $input['language'] ?? '';
        $race = $input['race'] ?? '';
        $homePhoneEnc = !empty($input['home_phone']) ? EncryptionService::encrypt($input['home_phone']) : null;
        $workPhoneEnc = !empty($input['work_phone']) ? EncryptionService::encrypt($input['work_phone']) : null;
        $workPhoneExt = $input['work_phone_ext'] ?? '';
        $addressLine2 = $input['address_line2'] ?? '';
        $isPoBox = !empty($input['is_po_box']) ? 1 : 0;
        $county = $input['county'] ?? '';
        // Dual-write the first emergency contact into the legacy singular columns for
        // backward compat with any other reader that hasn't moved to emergency_contacts_json.
        $firstContact = $emergencyContacts[0] ?? [];
        $emergName = trim(($firstContact['first_name'] ?? '') . ' ' . ($firstContact['last_name'] ?? ''));
        $emergRel = $firstContact['relationship'] ?? '';
        $emergPhone = $firstContact['mobile_phone'] ?? '';
        $emergPhoneExt = '';

        $genderIdentity = $input['gender_identity'] ?? '';
        $pronouns = $input['pronouns'] ?? '';
        $nickname = $input['nickname'] ?? '';
        $suffix = $input['suffix'] ?? '';
        $maidenName = $input['maiden_name'] ?? '';
        $previousName = $input['previous_name'] ?? '';
        $patientIdsJson = is_array($input['patient_ids_json'] ?? null) ? json_encode($input['patient_ids_json']) : ($input['patient_ids_json'] ?? null);

        $previousAddressJson = is_array($input['previous_address_json'] ?? null) ? json_encode($input['previous_address_json']) : ($input['previous_address_json'] ?? null);
        $caregiversJson = is_array($input['caregivers_json'] ?? null) ? json_encode($input['caregivers_json']) : ($input['caregivers_json'] ?? null);
        $guarantorJson = is_array($input['guarantor_json'] ?? null) ? json_encode($input['guarantor_json']) : ($input['guarantor_json'] ?? null);

        $preferredCommunication = $input['preferred_communication'] ?? '';
        $emailNotifications = isset($input['email_notifications']) ? ($input['email_notifications'] ? 1 : 0) : 1;
        $textNotifications = isset($input['text_notifications']) ? ($input['text_notifications'] ? 1 : 0) : 1;
        $voiceNotifications = isset($input['voice_notifications']) ? ($input['voice_notifications'] ? 1 : 0) : 1;

        $phrInvitation = $input['phr_invitation'] ?? 'To Patient';
        $category = $input['category'] ?? '';
        $paymentSource = $input['payment_source'] ?? '';
        $bloodGroup = $input['blood_group'] ?? '';
        $ethnicity = $input['ethnicity'] ?? '';
        $smokingStatus = $input['smoking_status'] ?? '';
        $employmentStatus = $input['employment_status'] ?? '';
        $sexualOrientation = $input['sexual_orientation'] ?? '';
        $sexualOrientationDeclined = !empty($input['sexual_orientation_declined']) ? 1 : 0;
        $genderIdentityDeclined = !empty($input['gender_identity_declined']) ? 1 : 0;
        $languageDeclined = !empty($input['language_declined']) ? 1 : 0;
        $raceDeclined = !empty($input['race_declined']) ? 1 : 0;
        $ethnicityDeclined = !empty($input['ethnicity_declined']) ? 1 : 0;
        $motherMaidenFirstName = $input['mother_maiden_first_name'] ?? '';
        $motherMaidenLastName = $input['mother_maiden_last_name'] ?? '';
        $multipleBirth = !empty($input['multiple_birth']) ? 1 : 0;
        $aboutPatient = $input['about_patient'] ?? '';
        $hearSource = $input['hear_source'] ?? '';
        $hearSpecificSource = $input['hear_specific_source'] ?? '';
        $photoUrl = !empty($input['photo_url']) ? $input['photo_url'] : ($existingPatient['photo_url'] ?? '');

        $sql = "UPDATE patients SET
                    first_name_encrypted = ?, 
                    last_name_encrypted = ?, 
                    dob_encrypted = ?,
                    patient_match_hash = ?,
                    age = ?,
                    email = ?, 
                    phone_encrypted = ?, 
                    address_encrypted = ?, 
                    primary_provider_id = ?,
                    ssn_encrypted = ?,
                    middle_name = ?,
                    gender = ?,
                    marital_status = ?,
                    language = ?,
                    race = ?,
                    home_phone_encrypted = ?,
                    work_phone_encrypted = ?,
                    work_phone_ext = ?,
                    address_line2 = ?,
                    city = ?,
                    state = ?,
                    country = ?,
                    zip = ?,
                    is_po_box = ?,
                    county = ?,
                    emergency_contact_name = ?,
                    emergency_relationship = ?,
                    emergency_phone = ?,
                    emergency_phone_ext = ?,
                    gender_identity = ?,
                    pronouns = ?,
                    nickname = ?,
                    suffix = ?,
                    maiden_name = ?,
                    previous_name = ?,
                    patient_ids_json = ?,
                    previous_address_json = ?,
                    caregivers_json = ?,
                    guarantor_json = ?,
                    preferred_communication = ?,
                    email_notifications = ?,
                    text_notifications = ?,
                    voice_notifications = ?,
                    phr_invitation = ?,
                    category = ?,
                    payment_source = ?,
                    blood_group = ?,
                    ethnicity = ?,
                    smoking_status = ?,
                    employment_status = ?,
                    sexual_orientation = ?,
                    sexual_orientation_declined = ?,
                    mother_maiden_first_name = ?,
                    mother_maiden_last_name = ?,
                    multiple_birth = ?,
                    about_patient = ?,
                    hear_source = ?,
                    hear_specific_source = ?,
                    photo_url = ?,
                    facility_id = ?,
                    patient_status = ?,
                    communication_consent = ?,
                    emergency_contacts_json = ?,
                    gender_identity_declined = ?,
                    language_declined = ?,
                    race_declined = ?,
                    ethnicity_declined = ?
                WHERE id = ?";
        Database::query($sql, [
            $firstEnc, $lastEnc, $dobEnc, $matchHash, $age, $email, $phoneEnc, $addressEnc, $primaryProviderId,
            $ssnEnc, $middleName, $gender, $marital, $language, $race,
            $homePhoneEnc, $workPhoneEnc, $workPhoneExt, $addressLine2, $city, $state, $country, $zip, $isPoBox, $county,
            $emergName, $emergRel, $emergPhone, $emergPhoneExt,
            $genderIdentity, $pronouns, $nickname, $suffix, $maidenName, $previousName, $patientIdsJson,
            $previousAddressJson, $caregiversJson, $guarantorJson,
            $preferredCommunication, $emailNotifications, $textNotifications, $voiceNotifications,
            $phrInvitation, $category, $paymentSource, $bloodGroup, $ethnicity, $smokingStatus, $employmentStatus,
            $sexualOrientation, $sexualOrientationDeclined, $motherMaidenFirstName, $motherMaidenLastName, $multipleBirth,
            $aboutPatient, $hearSource, $hearSpecificSource, $photoUrl,
            $facilityId, $patientStatus, $communicationConsent, $emergencyContactsJson,
            $genderIdentityDeclined, $languageDeclined, $raceDeclined, $ethnicityDeclined,
            $id
        ]);

        if (!empty($insuranceProvider) || !empty($primaryMemberId) || !empty($insurancePolicy)) {
            $this->upsertInsurance((int)$id, 'Primary', [
                'provider' => $insuranceProvider, 'member_id' => $primaryMemberId, 'payer_id' => $input['payer_id'] ?? '',
                'policy_no' => $insurancePolicy, 'group_no' => $input['group_number'] ?? '', 'plan_name' => $input['plan_name'] ?? '',
                'effective_date' => !empty($input['effective_date']) ? $input['effective_date'] : null,
                'subscriber_name' => trim($primarySubFirst . ' ' . $primarySubLast), 'subscriber_first_name' => $primarySubFirst,
                'subscriber_last_name' => $primarySubLast, 'subscriber_dob' => $primarySubDob ?: null, 'subscriber_relationship' => $primarySubRel,
                'subscriber_employer' => $input['subscriber_employer'] ?? '', 'copay' => $input['copay'] ?? ($input['copay_amount'] ?? null),
                'insurance_phone' => $input['insurance_phone'] ?? null,
            ]);
        }

        $secondary = is_array($input['secondary_insurance'] ?? null) ? $input['secondary_insurance'] : [];
        if (!empty($secondary['provider']) || !empty($secondary['policy_no'])) {
            $secSubFirst = $secondary['subscriber_first_name'] ?? '';
            $secSubLast = $secondary['subscriber_last_name'] ?? '';
            $this->upsertInsurance((int)$id, 'Secondary', [
                'provider' => $secondary['provider'] ?? '', 'member_id' => $secondary['member_id'] ?? '',
                'payer_id' => $secondary['payer_id'] ?? '', 'policy_no' => $secondary['policy_no'] ?? '',
                'group_no' => $secondary['group_no'] ?? '', 'plan_name' => $secondary['plan_name'] ?? '',
                'effective_date' => !empty($secondary['effective_date']) ? $secondary['effective_date'] : null,
                'subscriber_name' => trim($secSubFirst . ' ' . $secSubLast), 'subscriber_first_name' => $secSubFirst,
                'subscriber_last_name' => $secSubLast, 'subscriber_dob' => !empty($secondary['subscriber_dob']) ? $secondary['subscriber_dob'] : null,
                'subscriber_relationship' => $secondary['subscriber_relationship'] ?? '', 'subscriber_employer' => $secondary['subscriber_employer'] ?? '',
                'copay' => $secondary['copay'] ?? null, 'insurance_phone' => $secondary['insurance_phone'] ?? null,
            ]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $id, 'Update Patient Record', 'Patient Directory', $id);

        echo json_encode(['status' => 'success', 'message' => 'Patient updated successfully.']);
    }

    public function updateInsurance(array $params): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is missing.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $insuranceProvider = $input['insurance_provider'] ?? ($input['primary_provider'] ?? '');
        $payerId = $input['payer_id'] ?? '';
        $insurancePolicy = $input['insurance_policy'] ?? ($input['policy_number'] ?? ($input['primary_policy_no'] ?? ''));
        $groupNo = $input['group_number'] ?? ($input['primary_group_no'] ?? '');
        $planName = $input['plan_name'] ?? ($input['insurance_plan_name'] ?? '');
        $effDate = !empty($input['effective_date']) ? $input['effective_date'] : (!empty($input['insurance_effective_date']) ? $input['insurance_effective_date'] : null);
        $subName = $input['subscriber_name'] ?? '';
        $subDob = !empty($input['subscriber_dob']) ? $input['subscriber_dob'] : null;
        $subRel = !empty($input['subscriber_relationship']) ? $input['subscriber_relationship'] : 'Self';
        $copayVal = $input['copay'] ?? ($input['copay_amount'] ?? null);
        $insPhoneVal = $input['insurance_phone'] ?? null;

        $patient = Database::fetch("SELECT id FROM patients WHERE id = ?", [$id]);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient record not found.']);
            return;
        }

        $this->upsertInsurance((int)$id, 'Primary', [
            'provider' => $insuranceProvider, 'member_id' => $input['member_id'] ?? '', 'payer_id' => $payerId,
            'policy_no' => $insurancePolicy, 'group_no' => $groupNo, 'plan_name' => $planName, 'effective_date' => $effDate,
            'subscriber_name' => $subName, 'subscriber_first_name' => $input['subscriber_first_name'] ?? '',
            'subscriber_last_name' => $input['subscriber_last_name'] ?? '', 'subscriber_dob' => $subDob,
            'subscriber_relationship' => $subRel, 'subscriber_employer' => $input['subscriber_employer'] ?? '',
            'copay' => $copayVal, 'insurance_phone' => $insPhoneVal,
        ]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $id, 'Update Patient Insurance', 'Patient Directory', $id);

        echo json_encode(['status' => 'success', 'message' => 'Primary Insurance updated & saved successfully.']);
    }

    public function delete(array $params): void {
        // Permanently erases the chart and every linked record: system administrators only.
        $this->checkAccess(Roles::ADMIN);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is missing.']);
            return;
        }

        try {
            // Log audit event BEFORE deleting the patient row so foreign key constraint (audit_logs.patient_id) is satisfied
            AuditLogger::log($_SESSION['user_id'] ?? 1, $_SESSION['username'] ?? 'system', $_SESSION['user_role'] ?? 'Super Admin', $id, 'Delete Patient Record', 'Patient Directory', $id);

            // Delete linked records safely
            $tablesToDelete = ['patient_insurance', 'appointments', 'clinical_notes', 'patient_documents', 'encounters', 'prescriptions'];
            foreach ($tablesToDelete as $table) {
                try {
                    Database::query("DELETE FROM {$table} WHERE patient_id = ?", [$id]);
                } catch (\Throwable $t) {}
            }

            Database::query("DELETE FROM patients WHERE id = ?", [$id]);

            echo json_encode(['status' => 'success', 'message' => 'Patient deleted successfully.']);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error deleting patient record: ' . $e->getMessage()]);
        }
    }
}
