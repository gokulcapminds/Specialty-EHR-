<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class ClinicalController {
    private function checkAccess(array $allowedRoles): void {
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $allowedRoles)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    // ---------------------------------------------------------------
    // Internal helper — calculate BMI from lbs + inches (imperial)
    // Returns null if either value is missing or non-positive.
    // ---------------------------------------------------------------
    private function calculateBmi(?float $weightLbs, ?float $heightIn): ?float {
        if (!$weightLbs || !$heightIn || $weightLbs <= 0 || $heightIn <= 0) {
            return null;
        }
        return round(($weightLbs * 703) / ($heightIn * $heightIn), 1);
    }

    // ---------------------------------------------------------------
    // Validate a numeric vital value — must be positive if provided.
    // Returns the cast float or null; sets $error message on failure.
    // ---------------------------------------------------------------
    private function validateVital(mixed $value, string $label, ?string &$error): ?float {
        if ($value === null || $value === '') {
            return null;
        }
        $v = floatval($value);
        if ($v <= 0) {
            $error = "$label must be a positive number.";
            return null;
        }
        return $v;
    }

    // ---------------------------------------------------------------
    // Check that the encounter is NOT locked before allowing edits.
    // Responds 423 and exits if locked.
    // ---------------------------------------------------------------
    private function assertNotLocked(int $noteId): void {
        $note = Database::fetch("SELECT lock_state FROM clinical_notes WHERE id = ?", [$noteId]);
        if ($note && (int)$note['lock_state'] === 1) {
            http_response_code(423);
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => 'error',
                'message' => 'This encounter is locked. Use the Addendum workflow to make corrections.'
            ]);
            exit();
        }
    }

    public function store(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $patientId = $input['patient_id'] ?? null;
        $encounterType = $input['encounter_type'] ?? 'General';
        
        // Vitals
        $vitalTemp = $input['vital_temp'] ?? null;
        $vitalBpSystolic = $input['vital_bp_systolic'] ?? null;
        $vitalBpDiastolic = $input['vital_bp_diastolic'] ?? null;
        $vitalHeartRate = $input['vital_heart_rate'] ?? null;
        $vitalRespRate = $input['vital_resp_rate'] ?? null;
        $vitalSpo2 = $input['vital_spo2'] ?? null;
        $vitalHeight = $input['vital_height'] ?? null;
        $vitalWeight = $input['vital_weight'] ?? null;
        $vitalBmi = null; // Always computed server-side; client value ignored (VIT-CORE-002/003)
        $vitalPainScore = isset($input['vital_pain_score']) && $input['vital_pain_score'] !== '' ? min(10, max(0, intval($input['vital_pain_score']))) : null;
        $vitalPulsePattern = $input['vital_pulse_pattern'] ?? null;
        $vitalPulseVolume = $input['vital_pulse_volume'] ?? null;

        // Allergies, Medical History, Medications, ICD-10 Diagnoses & Family History
        $allergies = $input['allergies'] ?? null;
        $pmh = $input['pmh'] ?? null;
        $currentMedications = $input['current_medications'] ?? null;
        $icd10Codes = $input['icd10_codes'] ?? null;
        $familyHistory = $input['family_history'] ?? null;
        $fmAssessment = isset($input['fm_assessment']) ? (is_array($input['fm_assessment']) ? json_encode($input['fm_assessment']) : $input['fm_assessment']) : null;
        $functionalAssessment = isset($input['functional_assessment']) ? (is_array($input['functional_assessment']) ? json_encode($input['functional_assessment']) : $input['functional_assessment']) : null;
        $primaryCareData = isset($input['primary_care_data']) ? (is_array($input['primary_care_data']) ? json_encode($input['primary_care_data']) : $input['primary_care_data']) : null;
        $pedsNewbornData = isset($input['peds_newborn_data']) ? (is_array($input['peds_newborn_data']) ? json_encode($input['peds_newborn_data']) : $input['peds_newborn_data']) : null;
        $pedsOneMonthData = isset($input['peds_one_month_data']) ? (is_array($input['peds_one_month_data']) ? json_encode($input['peds_one_month_data']) : $input['peds_one_month_data']) : null;
        $pedsTwoMonthData = isset($input['peds_two_month_data']) ? (is_array($input['peds_two_month_data']) ? json_encode($input['peds_two_month_data']) : $input['peds_two_month_data']) : null;
        $pedsFourMonthData = isset($input['peds_four_month_data']) ? (is_array($input['peds_four_month_data']) ? json_encode($input['peds_four_month_data']) : $input['peds_four_month_data']) : null;
        $pedsSixMonthData = isset($input['peds_six_month_data']) ? (is_array($input['peds_six_month_data']) ? json_encode($input['peds_six_month_data']) : $input['peds_six_month_data']) : null;
        $pedsNineMonthData = isset($input['peds_nine_month_data']) ? (is_array($input['peds_nine_month_data']) ? json_encode($input['peds_nine_month_data']) : $input['peds_nine_month_data']) : null;
        $pedsTwelveMonthData = isset($input['peds_twelve_month_data']) ? (is_array($input['peds_twelve_month_data']) ? json_encode($input['peds_twelve_month_data']) : $input['peds_twelve_month_data']) : null;
        $pedsFifteenMonthData = isset($input['peds_fifteen_month_data']) ? (is_array($input['peds_fifteen_month_data']) ? json_encode($input['peds_fifteen_month_data']) : $input['peds_fifteen_month_data']) : null;
        $pedsEighteenMonthData = isset($input['peds_eighteen_month_data']) ? (is_array($input['peds_eighteen_month_data']) ? json_encode($input['peds_eighteen_month_data']) : $input['peds_eighteen_month_data']) : null;

        // SOAP
        $chiefComplaint = $input['chief_complaint'] ?? '';
        $hpi = $input['hpi'] ?? '';
        $ros = $input['ros'] ?? '';

        // Physical Exam
        $peGeneral = $input['pe_general'] ?? '';
        $peHeent = $input['pe_heent'] ?? '';
        $peCardio = $input['pe_cardio'] ?? '';
        $peResp = $input['pe_resp'] ?? '';
        $peAbdomen = $input['pe_abdomen'] ?? '';
        $peNeuro = $input['pe_neuro'] ?? '';
        $peSkin = $input['pe_skin'] ?? '';

        // Pediatrics Specific
        $growthWeightPercentile = $input['growth_weight_percentile'] ?? null;
        $growthHeightPercentile = $input['growth_height_percentile'] ?? null;
        $immunizationsAdministered = isset($input['immunizations_administered']) ? json_encode($input['immunizations_administered']) : null;

        // OB/GYN Specific
        $obgynLmp = !empty($input['obgyn_lmp']) ? $input['obgyn_lmp'] : null;
        $obgynEdd = !empty($input['obgyn_edd']) ? $input['obgyn_edd'] : null;
        $obgynGravida = isset($input['obgyn_gravida']) && $input['obgyn_gravida'] !== '' ? intval($input['obgyn_gravida']) : null;
        $obgynPara = isset($input['obgyn_para']) && $input['obgyn_para'] !== '' ? intval($input['obgyn_para']) : null;
        $obgynAbortions = isset($input['obgyn_abortions']) && $input['obgyn_abortions'] !== '' ? intval($input['obgyn_abortions']) : null;
        $obgynLiving = isset($input['obgyn_living']) && $input['obgyn_living'] !== '' ? intval($input['obgyn_living']) : null;
        $obgynFundalHeight = $input['obgyn_fundal_height'] ?? null;
        $obgynFetalHeartRate = $input['obgyn_fetal_heart_rate'] ?? null;

        $pediatricData = $input['pediatric_data'] ?? null;
        $obgynData = $input['obgyn_data'] ?? null;

        // 7 Specialty EHR data fields
        $orthoData   = isset($input['ortho_data'])   ? (is_array($input['ortho_data'])   ? json_encode($input['ortho_data'])   : $input['ortho_data'])   : null;
        $dermaData   = isset($input['derma_data'])   ? (is_array($input['derma_data'])   ? json_encode($input['derma_data'])   : $input['derma_data'])   : null;
        $neuroData   = isset($input['neuro_data'])   ? (is_array($input['neuro_data'])   ? json_encode($input['neuro_data'])   : $input['neuro_data'])   : null;
        $oncoData    = isset($input['onco_data'])    ? (is_array($input['onco_data'])    ? json_encode($input['onco_data'])    : $input['onco_data'])    : null;
        $ophthalData = isset($input['ophthal_data']) ? (is_array($input['ophthal_data']) ? json_encode($input['ophthal_data']) : $input['ophthal_data']) : null;
        $ptData      = isset($input['pt_data'])      ? (is_array($input['pt_data'])      ? json_encode($input['pt_data'])      : $input['pt_data'])      : null;
        $cardioData  = isset($input['cardio_data'])  ? (is_array($input['cardio_data'])  ? json_encode($input['cardio_data'])  : $input['cardio_data'])  : null;

        $summary = $input['summary'] ?? '';
        
        // Signatures
        $signedSignatureData = $input['signed_signature_data'] ?? null;
        $signedByName = null;
        $signedByCredentials = null;
        $signedAt = $input['signed_at'] ?? null;

        if (!empty($signedSignatureData)) {
            $signedByName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: ($_SESSION['username'] ?? 'Provider');
            $signedByCredentials = ($_SESSION['user_role'] ?? '') === 'Doctor' ? 'MD' : 'Clinician';
            if (empty($signedAt)) {
                $signedAt = date('Y-m-d H:i:s');
            }
        }

        // --- Server-side vitals validation (VIT-CORE-004) ---
        $vitalError = null;
        $vitalTemp      = $this->validateVital($vitalTemp,      'Temperature',      $vitalError);
        $vitalBpSystolic  = $this->validateVital($vitalBpSystolic,  'BP Systolic',      $vitalError);
        $vitalBpDiastolic = $this->validateVital($vitalBpDiastolic, 'BP Diastolic',     $vitalError);
        $vitalHeartRate   = $this->validateVital($vitalHeartRate,   'Heart Rate',       $vitalError);
        $vitalRespRate    = $this->validateVital($vitalRespRate,    'Respiratory Rate', $vitalError);
        $vitalSpo2        = $this->validateVital($vitalSpo2,        'SpO2',             $vitalError);
        $vitalHeight      = $this->validateVital($vitalHeight,      'Height',           $vitalError);
        $vitalWeight      = $this->validateVital($vitalWeight,      'Weight',           $vitalError);
        if ($vitalError) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => $vitalError]);
            return;
        }
        // Auto-calculate BMI (VIT-CORE-002/003)
        $vitalBmi = $this->calculateBmi($vitalWeight, $vitalHeight);

        // Encounter metadata
        $appointmentId  = !empty($input['appointment_id'])  ? intval($input['appointment_id'])  : null;
        $visitType      = $input['visit_type']      ?? null;
        $encounterMode  = $input['encounter_mode']  ?? 'In-Person';
        $encounterStatus = $input['encounter_status'] ?? 'in_progress';
        $allowedStatuses = ['draft','in_progress','ready_for_sign'];
        if (!in_array($encounterStatus, $allowedStatuses)) {
            $encounterStatus = 'in_progress';
        }

        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $noteDate = !empty($input['note_date']) ? date('Y-m-d H:i:s', strtotime($input['note_date'])) : date('Y-m-d H:i:s');

        $sql = "INSERT INTO clinical_notes 
                (patient_id, appointment_id, visit_type, encounter_mode, provider_id, note_date, encounter_type, encounter_status,
                 vital_temp, vital_bp_systolic, vital_bp_diastolic, vital_heart_rate, vital_resp_rate, vital_spo2,
                 vital_height, vital_weight, vital_bmi, vital_pain_score, vital_pulse_pattern, vital_pulse_volume,
                 allergies, pmh, current_medications, icd10_codes, family_history,
                 fm_assessment, functional_assessment, primary_care_data,
                 peds_newborn_data, peds_one_month_data, peds_two_month_data, peds_four_month_data,
                 peds_six_month_data, peds_nine_month_data, peds_twelve_month_data,
                 peds_fifteen_month_data, peds_eighteen_month_data,
                 chief_complaint, hpi, ros,
                 pe_general, pe_heent, pe_cardio, pe_resp, pe_abdomen, pe_neuro, pe_skin,
                 growth_weight_percentile, growth_height_percentile, immunizations_administered,
                 obgyn_lmp, obgyn_edd, obgyn_gravida, obgyn_para, obgyn_abortions, obgyn_living,
                 obgyn_fundal_height, obgyn_fetal_heart_rate,
                 pediatric_data, obgyn_data,
                 ortho_data, derma_data, neuro_data, onco_data, ophthal_data, pt_data, cardio_data,
                 clinical_summary, signed_by_name, signed_by_credentials, signed_at, signed_signature_data) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        Database::query($sql, [
            $patientId, $appointmentId, $visitType, $encounterMode,
            $_SESSION['user_id'], $noteDate, $encounterType, $encounterStatus,
            $vitalTemp, $vitalBpSystolic, $vitalBpDiastolic, $vitalHeartRate,
            $vitalRespRate, $vitalSpo2, $vitalHeight, $vitalWeight, $vitalBmi,
            $vitalPainScore, $vitalPulsePattern, $vitalPulseVolume,
            $allergies, $pmh, $currentMedications, $icd10Codes, $familyHistory,
            $fmAssessment, $functionalAssessment, $primaryCareData,
            $pedsNewbornData, $pedsOneMonthData, $pedsTwoMonthData, $pedsFourMonthData,
            $pedsSixMonthData, $pedsNineMonthData, $pedsTwelveMonthData,
            $pedsFifteenMonthData, $pedsEighteenMonthData,
            $chiefComplaint, $hpi, $ros,
            $peGeneral, $peHeent, $peCardio, $peResp, $peAbdomen, $peNeuro, $peSkin,
            $growthWeightPercentile, $growthHeightPercentile, $immunizationsAdministered,
            $obgynLmp, $obgynEdd, $obgynGravida, $obgynPara, $obgynAbortions, $obgynLiving,
            $obgynFundalHeight, $obgynFetalHeartRate,
            $pediatricData, $obgynData,
            $orthoData, $dermaData, $neuroData, $oncoData, $ophthalData, $ptData, $cardioData,
            $summary, $signedByName, $signedByCredentials, $signedAt, $signedSignatureData
        ]);
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Save Primary Care Encounter Note', 'Clinical Workspace', $newId);

        echo json_encode([
            'status' => 'success',
            'message' => 'Clinical encounter note saved successfully.'
        ]);
    }

    public function show(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Billing Staff']);
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID is missing.']);
            return;
        }

        $sql = "SELECT n.*, u.first_name, u.last_name 
                FROM clinical_notes n
                JOIN users u ON n.provider_id = u.id
                WHERE n.patient_id = ?
                ORDER BY n.note_date DESC";
        
        $notes = Database::fetchAll($sql, [$patientId]);

        foreach ($notes as &$note) {
            if ($note['immunizations_administered']) {
                $note['immunizations_administered'] = json_decode($note['immunizations_administered'], true);
            }
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'View Clinical History', 'Clinical Workspace');

        echo json_encode(['status' => 'success', 'data' => $notes]);
    }

    public function update(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        // SIGN-004: Locked encounters cannot be edited normally.
        $this->assertNotLocked((int)$id);

        $input = json_decode(file_get_contents('php://input'), true);
        $encounterType = $input['encounter_type'] ?? 'General';
        $providerId = $input['provider_id'] ?? null;
        $noteDate = !empty($input['note_date']) ? date('Y-m-d H:i:s', strtotime($input['note_date'])) : null;
        
        // Vitals
        $vitalTemp = $input['vital_temp'] ?? null;
        $vitalBpSystolic = $input['vital_bp_systolic'] ?? null;
        $vitalBpDiastolic = $input['vital_bp_diastolic'] ?? null;
        $vitalHeartRate = $input['vital_heart_rate'] ?? null;
        $vitalRespRate = $input['vital_resp_rate'] ?? null;
        $vitalSpo2 = $input['vital_spo2'] ?? null;
        $vitalHeight = $input['vital_height'] ?? null;
        $vitalWeight = $input['vital_weight'] ?? null;
        $vitalBmi = null; // Recalculated server-side
        $vitalPainScore = isset($input['vital_pain_score']) && $input['vital_pain_score'] !== '' ? min(10, max(0, intval($input['vital_pain_score']))) : null;
        $vitalPulsePattern = $input['vital_pulse_pattern'] ?? null;
        $vitalPulseVolume = $input['vital_pulse_volume'] ?? null;

        // Allergies, Medical History, Medications, ICD-10 Diagnoses & Family History
        $allergies = $input['allergies'] ?? null;
        $pmh = $input['pmh'] ?? null;
        $currentMedications = $input['current_medications'] ?? null;
        $icd10Codes = $input['icd10_codes'] ?? null;
        $familyHistory = $input['family_history'] ?? null;
        $fmAssessment = isset($input['fm_assessment']) ? (is_array($input['fm_assessment']) ? json_encode($input['fm_assessment']) : $input['fm_assessment']) : null;
        $functionalAssessment = isset($input['functional_assessment']) ? (is_array($input['functional_assessment']) ? json_encode($input['functional_assessment']) : $input['functional_assessment']) : null;
        $primaryCareData = isset($input['primary_care_data']) ? (is_array($input['primary_care_data']) ? json_encode($input['primary_care_data']) : $input['primary_care_data']) : null;
        $pedsNewbornData = isset($input['peds_newborn_data']) ? (is_array($input['peds_newborn_data']) ? json_encode($input['peds_newborn_data']) : $input['peds_newborn_data']) : null;
        $pedsOneMonthData = isset($input['peds_one_month_data']) ? (is_array($input['peds_one_month_data']) ? json_encode($input['peds_one_month_data']) : $input['peds_one_month_data']) : null;
        $pedsTwoMonthData = isset($input['peds_two_month_data']) ? (is_array($input['peds_two_month_data']) ? json_encode($input['peds_two_month_data']) : $input['peds_two_month_data']) : null;
        $pedsFourMonthData = isset($input['peds_four_month_data']) ? (is_array($input['peds_four_month_data']) ? json_encode($input['peds_four_month_data']) : $input['peds_four_month_data']) : null;
        $pedsSixMonthData = isset($input['peds_six_month_data']) ? (is_array($input['peds_six_month_data']) ? json_encode($input['peds_six_month_data']) : $input['peds_six_month_data']) : null;
        $pedsNineMonthData = isset($input['peds_nine_month_data']) ? (is_array($input['peds_nine_month_data']) ? json_encode($input['peds_nine_month_data']) : $input['peds_nine_month_data']) : null;
        $pedsTwelveMonthData = isset($input['peds_twelve_month_data']) ? (is_array($input['peds_twelve_month_data']) ? json_encode($input['peds_twelve_month_data']) : $input['peds_twelve_month_data']) : null;
        $pedsFifteenMonthData = isset($input['peds_fifteen_month_data']) ? (is_array($input['peds_fifteen_month_data']) ? json_encode($input['peds_fifteen_month_data']) : $input['peds_fifteen_month_data']) : null;
        $pedsEighteenMonthData = isset($input['peds_eighteen_month_data']) ? (is_array($input['peds_eighteen_month_data']) ? json_encode($input['peds_eighteen_month_data']) : $input['peds_eighteen_month_data']) : null;

        // SOAP
        $chiefComplaint = $input['chief_complaint'] ?? '';
        $hpi = $input['hpi'] ?? '';
        $ros = $input['ros'] ?? '';

        // Physical Exam
        $peGeneral = $input['pe_general'] ?? '';
        $peHeent = $input['pe_heent'] ?? '';
        $peCardio = $input['pe_cardio'] ?? '';
        $peResp = $input['pe_resp'] ?? '';
        $peAbdomen = $input['pe_abdomen'] ?? '';
        $peNeuro = $input['pe_neuro'] ?? '';
        $peSkin = $input['pe_skin'] ?? '';

        // Pediatrics Specific
        $growthWeightPercentile = $input['growth_weight_percentile'] ?? null;
        $growthHeightPercentile = $input['growth_height_percentile'] ?? null;
        $immunizationsAdministered = isset($input['immunizations_administered']) ? json_encode($input['immunizations_administered']) : null;

        // OB/GYN Specific
        $obgynLmp = !empty($input['obgyn_lmp']) ? $input['obgyn_lmp'] : null;
        $obgynEdd = !empty($input['obgyn_edd']) ? $input['obgyn_edd'] : null;
        $obgynGravida = isset($input['obgyn_gravida']) && $input['obgyn_gravida'] !== '' ? intval($input['obgyn_gravida']) : null;
        $obgynPara = isset($input['obgyn_para']) && $input['obgyn_para'] !== '' ? intval($input['obgyn_para']) : null;
        $obgynAbortions = isset($input['obgyn_abortions']) && $input['obgyn_abortions'] !== '' ? intval($input['obgyn_abortions']) : null;
        $obgynLiving = isset($input['obgyn_living']) && $input['obgyn_living'] !== '' ? intval($input['obgyn_living']) : null;
        $obgynFundalHeight = $input['obgyn_fundal_height'] ?? null;
        $obgynFetalHeartRate = $input['obgyn_fetal_heart_rate'] ?? null;

        $pediatricData = $input['pediatric_data'] ?? null;
        $obgynData = $input['obgyn_data'] ?? null;

        // 7 Specialty EHR data fields
        $orthoData   = isset($input['ortho_data'])   ? (is_array($input['ortho_data'])   ? json_encode($input['ortho_data'])   : $input['ortho_data'])   : null;
        $dermaData   = isset($input['derma_data'])   ? (is_array($input['derma_data'])   ? json_encode($input['derma_data'])   : $input['derma_data'])   : null;
        $neuroData   = isset($input['neuro_data'])   ? (is_array($input['neuro_data'])   ? json_encode($input['neuro_data'])   : $input['neuro_data'])   : null;
        $oncoData    = isset($input['onco_data'])    ? (is_array($input['onco_data'])    ? json_encode($input['onco_data'])    : $input['onco_data'])    : null;
        $ophthalData = isset($input['ophthal_data']) ? (is_array($input['ophthal_data']) ? json_encode($input['ophthal_data']) : $input['ophthal_data']) : null;
        $ptData      = isset($input['pt_data'])      ? (is_array($input['pt_data'])      ? json_encode($input['pt_data'])      : $input['pt_data'])      : null;
        $cardioData  = isset($input['cardio_data'])  ? (is_array($input['cardio_data'])  ? json_encode($input['cardio_data'])  : $input['cardio_data'])  : null;

        $summary = $input['summary'] ?? '';
        
        // Signatures
        $signedSignatureData = $input['signed_signature_data'] ?? null;
        $signedByName = null;
        $signedByCredentials = null;
        $signedAt = $input['signed_at'] ?? null;

        if (!empty($signedSignatureData)) {
            $signedByName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: ($_SESSION['username'] ?? 'Provider');
            $signedByCredentials = ($_SESSION['user_role'] ?? '') === 'Doctor' ? 'MD' : 'Clinician';
            if (empty($signedAt)) {
                $signedAt = date('Y-m-d H:i:s');
            }
        }

        $sql = "UPDATE clinical_notes SET 
                provider_id = IFNULL(?, provider_id),
                note_date = IFNULL(?, note_date),
                encounter_type = ?, vital_temp = ?, vital_bp_systolic = ?, vital_bp_diastolic = ?, 
                vital_heart_rate = ?, vital_resp_rate = ?, vital_spo2 = ?, vital_height = ?, vital_weight = ?, vital_bmi = ?, vital_pulse_pattern = ?, vital_pulse_volume = ?, 
                allergies = ?, pmh = ?, current_medications = ?, icd10_codes = ?, family_history = ?, fm_assessment = ?, functional_assessment = ?, primary_care_data = ?, peds_newborn_data = ?, peds_one_month_data = ?, peds_two_month_data = ?, peds_four_month_data = ?, peds_six_month_data = ?, peds_nine_month_data = ?, peds_twelve_month_data = ?, peds_fifteen_month_data = ?, peds_eighteen_month_data = ?, 
                chief_complaint = ?, hpi = ?, ros = ?, pe_general = ?, pe_heent = ?, pe_cardio = ?, pe_resp = ?, 
                pe_abdomen = ?, pe_neuro = ?, pe_skin = ?, growth_weight_percentile = ?, growth_height_percentile = ?, 
                immunizations_administered = ?, obgyn_lmp = ?, obgyn_edd = ?, obgyn_gravida = ?, obgyn_para = ?, 
                obgyn_abortions = ?, obgyn_living = ?, obgyn_fundal_height = ?, obgyn_fetal_heart_rate = ?, pediatric_data = ?, obgyn_data = ?, 
                ortho_data = ?, derma_data = ?, neuro_data = ?, onco_data = ?, ophthal_data = ?, pt_data = ?, cardio_data = ?,
                clinical_summary = ?, signed_by_name = ?, signed_by_credentials = ?, signed_at = ?, signed_signature_data = ? 
                WHERE id = ?";
        
        Database::query($sql, [
            $providerId,
            $noteDate,
            $encounterType,
            $vitalTemp,
            $vitalBpSystolic,
            $vitalBpDiastolic,
            $vitalHeartRate,
            $vitalRespRate,
            $vitalSpo2,
            $vitalHeight,
            $vitalWeight,
            $vitalBmi,
            $vitalPulsePattern,
            $vitalPulseVolume,
            $allergies,
            $pmh,
            $currentMedications,
            $icd10Codes,
            $familyHistory,
            $fmAssessment,
            $functionalAssessment,
            $primaryCareData,
            $pedsNewbornData,
            $pedsOneMonthData,
            $pedsTwoMonthData,
            $pedsFourMonthData,
            $pedsSixMonthData,
            $pedsNineMonthData,
            $pedsTwelveMonthData,
            $pedsFifteenMonthData,
            $pedsEighteenMonthData,
            $chiefComplaint,
            $hpi,
            $ros,
            $peGeneral,
            $peHeent,
            $peCardio,
            $peResp,
            $peAbdomen,
            $peNeuro,
            $peSkin,
            $growthWeightPercentile,
            $growthHeightPercentile,
            $immunizationsAdministered,
            $obgynLmp,
            $obgynEdd,
            $obgynGravida,
            $obgynPara,
            $obgynAbortions,
            $obgynLiving,
            $obgynFundalHeight,
            $obgynFetalHeartRate,
            $pediatricData,
            $obgynData,
            $orthoData,
            $dermaData,
            $neuroData,
            $oncoData,
            $ophthalData,
            $ptData,
            $cardioData,
            $summary,
            $signedByName,
            $signedByCredentials,
            $signedAt,
            $signedSignatureData,
            $id
        ]);

        echo json_encode([
            'status' => 'success',
            'message' => 'Clinical encounter note updated successfully.'
        ]);
    }

    public function delete($params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        $note = Database::fetch("SELECT * FROM clinical_notes WHERE id = ?", [$id]);
        if (!$note) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Clinical note record not found.']);
            return;
        }

        Database::query("DELETE FROM clinical_notes WHERE id = ?", [$id]);
        AuditLogger::log(
            $_SESSION['user_id'] ?? null,
            $_SESSION['username'] ?? 'system',
            $_SESSION['user_role'] ?? 'Doctor',
            $note['patient_id'] ?? null,
            'Delete Clinical Note',
            'Clinical Workspace',
            (int)$id
        );

        echo json_encode(['status' => 'success', 'message' => 'Clinical note deleted successfully.']);
    }

    public function searchIcd10(): void {
        header('Content-Type: application/json');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 1) {
            echo json_encode(['status' => 'success', 'data' => []]);
            return;
        }

        $term = '%' . $q . '%';
        $sql = "SELECT dx_code, formatted_dx_code, short_desc, long_desc 
                FROM icd10_dx_order_code 
                WHERE formatted_dx_code LIKE ? OR dx_code LIKE ? OR short_desc LIKE ? OR long_desc LIKE ? 
                LIMIT 30";
        $results = Database::fetchAll($sql, [$term, $term, $term, $term]);
        echo json_encode(['status' => 'success', 'data' => $results]);
    }

    public function getSingleNote(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Billing Staff']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Note ID missing.']);
            return;
        }

        $note = Database::fetch(
            "SELECT n.*, u.first_name, u.last_name 
             FROM clinical_notes n
             LEFT JOIN users u ON n.provider_id = u.id
             WHERE n.id = ?",
            [$id]
        );

        if (!$note) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Encounter note not found.']);
            return;
        }

        if (!empty($note['immunizations_administered'])) {
            $note['immunizations_administered'] = json_decode($note['immunizations_administered'], true);
        }

        echo json_encode(['status' => 'success', 'data' => $note]);
    }

    // ---------------------------------------------------------------
    // POST /api/clinical/notes/{id}/sign
    // Sign & Finalize — SIGN-001 to SIGN-007
    // Only Doctors may call this endpoint.
    // ---------------------------------------------------------------
    public function signAndLock(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        $note = Database::fetch("SELECT * FROM clinical_notes WHERE id = ?", [$id]);
        if (!$note) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Encounter not found.']);
            return;
        }

        // Already locked
        if ((int)$note['lock_state'] === 1) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'Encounter is already signed and locked.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        // SIGN-002: Validate required clinical information
        $errors = [];
        if (empty($note['chief_complaint'])) $errors[] = 'Chief Complaint is required before signing.';
        if (empty($note['icd10_codes']))     $errors[] = 'At least one ICD-10 Diagnosis code is required before signing.';
        if (empty($note['provider_id']))     $errors[] = 'Provider must be assigned to this encounter.';
        if (!empty($errors)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => implode(' | ', $errors)]);
            return;
        }

        // Capture signature data
        $signedSignatureData = $input['signed_signature_data'] ?? $note['signed_signature_data'];
        $signedByName = trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')) ?: ($_SESSION['username'] ?? 'Provider');
        $signedByCredentials = ($_SESSION['user_role'] ?? '') === 'Doctor' ? 'MD' : 'Clinician';
        $signedAt = date('Y-m-d H:i:s');
        $signedByUserId = $_SESSION['user_id'];

        Database::query(
            "UPDATE clinical_notes
             SET encounter_status = 'locked',
                 lock_state = 1,
                 locked_at = ?,
                 billing_queue_status = 'pending_review',
                 signed_by_user_id = ?,
                 signed_by_name = ?,
                 signed_by_credentials = ?,
                 signed_at = ?,
                 signed_signature_data = COALESCE(?, signed_signature_data)
             WHERE id = ?",
            [$signedAt, $signedByUserId, $signedByName, $signedByCredentials,
             $signedAt, $signedSignatureData, $id]
        );

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            $note['patient_id'],
            'Sign & Lock Encounter',
            'Clinical Workspace',
            (int)$id
        );

        echo json_encode([
            'status'  => 'success',
            'message' => 'Encounter signed and locked successfully.',
            'signed_at' => $signedAt
        ]);
    }

    // ---------------------------------------------------------------
    // POST /api/clinical/notes/{id}/addendum
    // Appends a signed addendum to a locked encounter.
    // Original note content is preserved (SIGN-005, SIGN-006).
    // ---------------------------------------------------------------
    public function addAddendum(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        $note = Database::fetch("SELECT lock_state, addendums, patient_id FROM clinical_notes WHERE id = ?", [$id]);
        if (!$note) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Encounter not found.']);
            return;
        }

        if ((int)$note['lock_state'] !== 1) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Addendums can only be added to locked encounters.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $addendumNote = trim($input['addendum_note'] ?? '');
        if (empty($addendumNote)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Addendum note text is required.']);
            return;
        }

        $existing = !empty($note['addendums']) ? json_decode($note['addendums'], true) : [];
        $newEntry = [
            'id'        => count($existing) + 1,
            'user_id'   => $_SESSION['user_id'],
            'user_name' => trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')),
            'user_role' => $_SESSION['user_role'] ?? '',
            'timestamp' => date('Y-m-d H:i:s'),
            'note'      => $addendumNote,
            'sig_data'  => $input['signed_signature_data'] ?? null,
        ];
        $existing[] = $newEntry;

        Database::query(
            "UPDATE clinical_notes SET addendums = ? WHERE id = ?",
            [json_encode($existing), $id]
        );

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            $note['patient_id'],
            'Add Addendum to Locked Encounter',
            'Clinical Workspace',
            (int)$id
        );

        echo json_encode([
            'status'  => 'success',
            'message' => 'Addendum added successfully.',
            'addendum' => $newEntry
        ]);
    }

    // ---------------------------------------------------------------
    // PUT /api/clinical/notes/{id}/status
    // Allows transitioning encounter status (draft → in_progress → ready_for_sign).
    // Transitions to signed/locked are only via signAndLock().
    // ---------------------------------------------------------------
    public function updateStatus(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Encounter ID required.']);
            return;
        }

        $this->assertNotLocked((int)$id);

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $newStatus = $input['status'] ?? '';
        $allowed = ['draft', 'in_progress', 'ready_for_sign'];
        if (!in_array($newStatus, $allowed)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Invalid status transition. Use the Sign & Lock endpoint to finalize.']);
            return;
        }

        Database::query(
            "UPDATE clinical_notes SET encounter_status = ? WHERE id = ?",
            [$newStatus, $id]
        );

        echo json_encode(['status' => 'success', 'message' => "Encounter status updated to '$newStatus'."]);
    }
}
