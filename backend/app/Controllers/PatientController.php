<?php
namespace App\Controllers;

use App\Models\Database;
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

    public function providers(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
        header('Content-Type: application/json');

        $sql = "SELECT id, first_name, last_name, role, specialty FROM users WHERE role IN ('Doctor', 'Therapist', 'Super Admin') ORDER BY first_name ASC";
        $providers = Database::fetchAll($sql);
        echo json_encode(['status' => 'success', 'data' => $providers]);
    }

    public function show(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
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
                       pi.primary_provider as ins_provider,
                       pi.primary_policy_no as ins_policy,
                       pi.primary_group_no as ins_group,
                       pi.insurance_phone as ins_phone,
                       pif.status as intake_status
                FROM patients p
                LEFT JOIN users u ON p.primary_provider_id = u.id
                LEFT JOIN patient_insurance pi ON p.id = pi.patient_id
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

        echo json_encode(['status' => 'success', 'data' => $pt]);
    }

    public function index(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
        header('Content-Type: application/json');

        $search = $_GET['search'] ?? '';
        
        $sql = "SELECT p.*, 
                       u.first_name as provider_first_name, 
                       u.last_name as provider_last_name,
                       pi.primary_provider as ins_provider,
                       pi.primary_policy_no as ins_policy,
                       pi.primary_group_no as ins_group,
                       pi.insurance_phone as ins_phone,
                       pi.plan_name,
                       pi.payer_id,
                       pi.effective_date,
                       pi.subscriber_name,
                       pi.subscriber_dob,
                       pi.subscriber_relationship,
                       pi.copay,
                       pif.status as intake_status
                FROM patients p
                LEFT JOIN users u ON p.primary_provider_id = u.id
                LEFT JOIN patient_insurance pi ON p.id = pi.patient_id
                LEFT JOIN patient_intake_forms pif ON pif.id = (SELECT MAX(id) FROM patient_intake_forms WHERE patient_id = p.id)
                ORDER BY p.id DESC";
        $params = [];

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
                'emergency_contact_name' => $p['emergency_contact_name'] ?? '',
                'emergency_relationship' => $p['emergency_relationship'] ?? '',
                'emergency_phone' => $p['emergency_phone'] ?? '',
                'emergency_phone_ext' => $p['emergency_phone_ext'] ?? '',
                'created_at' => $p['created_at'],
                'primary_provider_id' => $p['primary_provider_id'],
                'assigned_provider_name' => $assignedProviderName,
                'insurance_provider' => $p['ins_provider'] ?? '',
                'payer_id' => $p['payer_id'] ?? '',
                'insurance_policy' => $p['ins_policy'] ?? '',
                'policy_number' => $p['ins_policy'] ?? '',
                'group_number' => $p['ins_group'] ?? '',
                'plan_name' => $p['plan_name'] ?? '',
                'insurance_plan_name' => $p['plan_name'] ?? '',
                'effective_date' => $p['effective_date'] ?? '',
                'insurance_effective_date' => $p['effective_date'] ?? '',
                'subscriber_name' => $p['subscriber_name'] ?? '',
                'subscriber_dob' => $p['subscriber_dob'] ?? '',
                'subscriber_relationship' => $p['subscriber_relationship'] ?? 'Self',
                'copay_amount' => $p['copay'] ?? '',
                'copay' => $p['copay'] ?? '',
                'insurance_phone' => $p['ins_phone'] ?? '',
                'intake_status' => $p['intake_status'] ?? '',
                'photo_url' => $p['photo_url'] ?? '',
                'photo' => $p['photo_url'] ?? ''
            ];
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'View Patient List', 'Patient Directory');

        echo json_encode(['status' => 'success', 'data' => $decryptedPatients]);
    }

    public function store(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
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
        $motherMaidenFirstName = $input['mother_maiden_first_name'] ?? '';
        $motherMaidenLastName = $input['mother_maiden_last_name'] ?? '';
        $multipleBirth = !empty($input['multiple_birth']) ? 1 : 0;
        $aboutPatient = $input['about_patient'] ?? '';
        $hearSource = $input['hear_source'] ?? '';
        $hearSpecificSource = $input['hear_specific_source'] ?? '';
        $photoUrl = $input['photo_url'] ?? '';

        if (empty($firstName) || empty($lastName) || empty($dob)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'First name, last name, and date of birth are required.']);
            return;
        }

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

        $sql = "INSERT INTO patients (
            first_name_encrypted, middle_name, last_name_encrypted, dob_encrypted, gender, age, ssn_encrypted, email,
            phone_encrypted, home_phone_encrypted, work_phone_encrypted, work_phone_ext, address_encrypted,
            address_line2, city, state, country, zip, is_po_box, county, primary_provider_id,
            gender_identity, pronouns, nickname, suffix, maiden_name, previous_name, linked_patient, patient_ids_json,
            previous_address_json, emergency_contact_name, emergency_phone, emergency_phone_ext, caregivers_json, guarantor_json, preferred_communication,
            email_notifications, text_notifications, voice_notifications, phr_invitation, category, payment_source,
            blood_group, language, race, ethnicity, smoking_status, marital_status, employment_status, sexual_orientation,
            sexual_orientation_declined, mother_maiden_first_name, mother_maiden_last_name, multiple_birth, about_patient,
            hear_source, hear_specific_source, photo_url
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?
        )";

        Database::query($sql, [
            $firstEnc, $middleName, $lastEnc, $dobEnc, $gender, $age, $ssnEnc, $email,
            $phoneEnc, $homePhoneEnc, $workPhoneEnc, $workPhoneExt, $addressEnc,
            $addressLine2, $city, $state, $country, $zip, $isPoBox, $county, $primaryProviderId,
            $genderIdentity, $pronouns, $nickname, $suffix, $maidenName, $previousName, $linkedPatient, $patientIdsJson,
            $previousAddressJson, $emergencyContactName, $emergencyPhone, $emergencyPhoneExt, $caregiversJson, $guarantorJson, $preferredCommunication,
            $emailNotifications, $textNotifications, $voiceNotifications, $phrInvitation, $category, $paymentSource,
            $bloodGroup, $language, $race, $ethnicity, $smokingStatus, $maritalStatus, $employmentStatus, $sexualOrientation,
            $sexualOrientationDeclined, $motherMaidenFirstName, $motherMaidenLastName, $multipleBirth, $aboutPatient,
            $hearSource, $hearSpecificSource, $photoUrl
        ]);
        $newId = Database::lastInsertId();

        if (!empty($insuranceProvider) || !empty($insurancePolicy)) {
            $payerId = $input['payer_id'] ?? '';
            $groupNo = $input['group_number'] ?? '';
            $planName = $input['plan_name'] ?? '';
            $effDate = !empty($input['effective_date']) ? $input['effective_date'] : null;
            $subName = $input['subscriber_name'] ?? '';
            $subDob = !empty($input['subscriber_dob']) ? $input['subscriber_dob'] : null;
            $subRel = $input['subscriber_relationship'] ?? '';
            $copayVal = $input['copay'] ?? ($input['copay_amount'] ?? null);
            $insPhoneVal = $input['insurance_phone'] ?? null;

            Database::query(
                "INSERT INTO patient_insurance (patient_id, primary_provider, payer_id, primary_policy_no, primary_group_no, plan_name, effective_date, subscriber_name, subscriber_dob, subscriber_relationship, copay, insurance_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$newId, $insuranceProvider, $payerId, $insurancePolicy, $groupNo, $planName, $effDate, $subName, $subDob, $subRel, $copayVal, $insPhoneVal]
            );
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $newId, 'Create Patient Record', 'Patient Directory', $newId);

        echo json_encode(['status' => 'success', 'message' => 'Patient created successfully.', 'id' => $newId]);
    }

    public function update(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
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

        if (empty($firstName) || empty($lastName) || empty($dob)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'First name, last name, and DOB are required.']);
            return;
        }

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
        $city = $input['city'] ?? '';
        $state = $input['state'] ?? '';
        $country = $input['country'] ?? 'United States';
        $zip = $input['zip'] ?? '';
        $isPoBox = !empty($input['is_po_box']) ? 1 : 0;
        $county = $input['county'] ?? '';
        $emergName = $input['emergency_contact_name'] ?? '';
        $emergRel = $input['emergency_relationship'] ?? '';
        $emergPhone = $input['emergency_phone'] ?? '';
        $emergPhoneExt = $input['emergency_phone_ext'] ?? '';

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
        $motherMaidenFirstName = $input['mother_maiden_first_name'] ?? '';
        $motherMaidenLastName = $input['mother_maiden_last_name'] ?? '';
        $multipleBirth = !empty($input['multiple_birth']) ? 1 : 0;
        $aboutPatient = $input['about_patient'] ?? '';
        $hearSource = $input['hear_source'] ?? '';
        $hearSpecificSource = $input['hear_specific_source'] ?? '';
        $photoUrl = !empty($input['photo_url']) ? $input['photo_url'] : ($existingPatient['photo_url'] ?? '');

        $payerId = $input['payer_id'] ?? '';
        $groupNo = $input['group_number'] ?? '';
        $planName = $input['plan_name'] ?? '';
        $effDate = !empty($input['effective_date']) ? $input['effective_date'] : null;
        $subName = $input['subscriber_name'] ?? '';
        $subDob = !empty($input['subscriber_dob']) ? $input['subscriber_dob'] : null;
        $subRel = $input['subscriber_relationship'] ?? '';

        $sql = "UPDATE patients SET 
                    first_name_encrypted = ?, 
                    last_name_encrypted = ?, 
                    dob_encrypted = ?, 
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
                    photo_url = ?
                WHERE id = ?";
        Database::query($sql, [
            $firstEnc, $lastEnc, $dobEnc, $age, $email, $phoneEnc, $addressEnc, $primaryProviderId,
            $ssnEnc, $middleName, $gender, $marital, $language, $race,
            $homePhoneEnc, $workPhoneEnc, $workPhoneExt, $addressLine2, $city, $state, $country, $zip, $isPoBox, $county,
            $emergName, $emergRel, $emergPhone, $emergPhoneExt,
            $genderIdentity, $pronouns, $nickname, $suffix, $maidenName, $previousName, $patientIdsJson,
            $previousAddressJson, $caregiversJson, $guarantorJson,
            $preferredCommunication, $emailNotifications, $textNotifications, $voiceNotifications,
            $phrInvitation, $category, $paymentSource, $bloodGroup, $ethnicity, $smokingStatus, $employmentStatus,
            $sexualOrientation, $sexualOrientationDeclined, $motherMaidenFirstName, $motherMaidenLastName, $multipleBirth,
            $aboutPatient, $hearSource, $hearSpecificSource, $photoUrl,
            $id
        ]);

        $copayVal = $input['copay'] ?? ($input['copay_amount'] ?? null);
        $insPhoneVal = $input['insurance_phone'] ?? null;

        $existingIns = Database::fetch("SELECT id FROM patient_insurance WHERE patient_id = ?", [$id]);
        if ($existingIns) {
            Database::query("UPDATE patient_insurance SET primary_provider = ?, payer_id = ?, primary_policy_no = ?, primary_group_no = ?, plan_name = ?, effective_date = ?, subscriber_name = ?, subscriber_dob = ?, subscriber_relationship = ?, copay = ?, insurance_phone = ? WHERE patient_id = ?", [
                $insuranceProvider, $payerId, $insurancePolicy, $groupNo, $planName, $effDate, $subName, $subDob, $subRel, $copayVal, $insPhoneVal, $id
            ]);
        } else if (!empty($insuranceProvider) || !empty($insurancePolicy)) {
            Database::query("INSERT INTO patient_insurance (patient_id, primary_provider, payer_id, primary_policy_no, primary_group_no, plan_name, effective_date, subscriber_name, subscriber_dob, subscriber_relationship, copay, insurance_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $id, $insuranceProvider, $payerId, $insurancePolicy, $groupNo, $planName, $effDate, $subName, $subDob, $subRel, $copayVal, $insPhoneVal
            ]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $id, 'Update Patient Record', 'Patient Directory', $id);

        echo json_encode(['status' => 'success', 'message' => 'Patient updated successfully.']);
    }

    public function updateInsurance(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
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

        $existingIns = Database::fetch("SELECT id FROM patient_insurance WHERE patient_id = ?", [$id]);
        if ($existingIns) {
            Database::query("UPDATE patient_insurance SET primary_provider = ?, payer_id = ?, primary_policy_no = ?, primary_group_no = ?, plan_name = ?, effective_date = ?, subscriber_name = ?, subscriber_dob = ?, subscriber_relationship = ?, copay = ?, insurance_phone = ? WHERE patient_id = ?", [
                $insuranceProvider, $payerId, $insurancePolicy, $groupNo, $planName, $effDate, $subName, $subDob, $subRel, $copayVal, $insPhoneVal, $id
            ]);
        } else {
            Database::query("INSERT INTO patient_insurance (patient_id, primary_provider, payer_id, primary_policy_no, primary_group_no, plan_name, effective_date, subscriber_name, subscriber_dob, subscriber_relationship, copay, insurance_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $id, $insuranceProvider, $payerId, $insurancePolicy, $groupNo, $planName, $effDate, $subName, $subDob, $subRel, $copayVal, $insPhoneVal
            ]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $id, 'Update Patient Insurance', 'Patient Directory', $id);

        echo json_encode(['status' => 'success', 'message' => 'Primary Insurance updated & saved successfully.']);
    }

    public function delete(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
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
