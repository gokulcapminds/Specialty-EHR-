<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;

/**
 * ImagingController
 *
 * Handles DICOM / medical imaging document metadata, file serving and AI analysis
 * endpoints.  All routes live under /api/imaging/.
 *
 * Route areas: maps under 'documents' via #^imaging(/|$)# → custom roles that
 * have document access inherit imaging access without a new RouteAreas entry.
 * (If you want a tighter gate, add a dedicated 'imaging' area later.)
 */
class ImagingController
{
    private function checkAccess(array $roles): void
    {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    // -------------------------------------------------------------------------
    // GET /api/imaging/studies?patient_id={id}
    // Returns all imaging-eligible documents (DICOM + images) for a patient.
    // -------------------------------------------------------------------------
    public function studies(array $params): void
    {
        $this->checkAccess(Roles::CLINICAL);
        header('Content-Type: application/json');

        $patientId = $_GET['patient_id'] ?? $params['patient_id'] ?? null;

        if ($patientId) {
            $sql = "SELECT d.id, d.patient_id, d.original_filename, d.mime_type, d.file_size,
                           d.modality, d.study_description, d.study_date,
                           d.ai_analysis_status, d.uploaded_at,
                           p.first_name AS patient_first_name, p.last_name AS patient_last_name,
                           CONCAT(u.first_name, ' ', u.last_name) AS uploaded_by_name
                    FROM patient_documents d
                    LEFT JOIN patients p ON d.patient_id = p.id
                    LEFT JOIN users u ON d.uploaded_by = u.id
                    WHERE d.patient_id = ?
                      AND (
                          d.mime_type IN ('application/dicom','application/octet-stream','image/jpeg','image/png','image/gif')
                          OR d.original_filename REGEXP '\\.(dcm|dicom|jpg|jpeg|png)$'
                      )
                    ORDER BY d.study_date DESC, d.uploaded_at DESC";
            $studies = Database::fetchAll($sql, [$patientId]);
        } else {
            $sql = "SELECT d.id, d.patient_id, d.original_filename, d.mime_type, d.file_size,
                           d.modality, d.study_description, d.study_date,
                           d.ai_analysis_status, d.uploaded_at,
                           p.first_name AS patient_first_name, p.last_name AS patient_last_name,
                           CONCAT(u.first_name, ' ', u.last_name) AS uploaded_by_name
                    FROM patient_documents d
                    LEFT JOIN patients p ON d.patient_id = p.id
                    LEFT JOIN users u ON d.uploaded_by = u.id
                    WHERE (
                          d.mime_type IN ('application/dicom','application/octet-stream','image/jpeg','image/png','image/gif')
                          OR d.original_filename REGEXP '\\.(dcm|dicom|jpg|jpeg|png)$'
                    )
                    ORDER BY d.study_date DESC, d.uploaded_at DESC";
            $studies = Database::fetchAll($sql);
        }

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            (int)$patientId, 'View Imaging Studies', 'Documents'
        );

        echo json_encode(['status' => 'success', 'data' => $studies]);
    }

    // -------------------------------------------------------------------------
    // GET /api/imaging/document/{id}
    // Streams the raw file so the browser canvas / DWV can load it.
    // -------------------------------------------------------------------------
    public function serveFile(array $params): void
    {
        $this->checkAccess(Roles::CLINICAL);

        $docId = $params['id'] ?? null;
        if (!$docId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Document ID required.']);
            return;
        }

        $doc = Database::fetch("SELECT * FROM patient_documents WHERE id = ?", [(int)$docId]);
        if (!$doc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Document not found.']);
            return;
        }

        $storageDir = dirname(__DIR__, 3) . '/storage/documents';
        $filePath   = $storageDir . '/' . $doc['stored_filename'];

        if (!file_exists($filePath)) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'File missing from storage.']);
            return;
        }

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            (int)$doc['patient_id'], 'View Imaging File (' . $doc['original_filename'] . ')', 'Documents', (int)$docId
        );

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . addslashes($doc['original_filename']) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=300');
        readfile($filePath);
        exit();
    }

    // -------------------------------------------------------------------------
    // POST /api/imaging/update-meta
    // Lets the Documents tab save modality / study_description / study_date when
    // a staff member sets them manually.
    // Body JSON: { document_id, modality, study_description, study_date }
    // -------------------------------------------------------------------------
    public function updateMeta(array $params): void
    {
        $this->checkAccess(Roles::CLINICAL);
        header('Content-Type: application/json');

        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $documentId = (int)($body['document_id'] ?? 0);
        $modality   = trim($body['modality'] ?? '');
        $desc       = trim($body['study_description'] ?? '');
        $date       = trim($body['study_date'] ?? '');

        if (!$documentId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'document_id required.']);
            return;
        }

        $doc = Database::fetch("SELECT id, patient_id FROM patient_documents WHERE id = ?", [$documentId]);
        if (!$doc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Document not found.']);
            return;
        }

        $allowed = ['X-RAY', 'MRI', 'CT', 'ULTRASOUND', 'ECG', 'OTHER', ''];
        if (!in_array(strtoupper($modality), $allowed, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid modality.']);
            return;
        }

        Database::query(
            "UPDATE patient_documents SET modality = ?, study_description = ?, study_date = ? WHERE id = ?",
            [
                $modality ?: null,
                $desc     ?: null,
                $date     ?: null,
                $documentId,
            ]
        );

        echo json_encode(['status' => 'success', 'message' => 'Imaging metadata updated.']);
    }

    // -------------------------------------------------------------------------
    // POST /api/imaging/analyze
    // Runs AI diagnostics on a loaded imaging document.
    // Body JSON: { document_id, modality, image_data_base64? }
    //
    // NOTE: In production this would POST to Hugging Face Inference API or an
    // on-premise model server.  Here we implement a rich, structured simulation
    // that mirrors real model output so the full UI workflow is exercisable.
    // -------------------------------------------------------------------------
    public function analyze(array $params): void
    {
        $this->checkAccess(Roles::CLINICAL);
        header('Content-Type: application/json');

        $body       = json_decode(file_get_contents('php://input'), true) ?? [];
        $documentId = (int)($body['document_id'] ?? 0);
        $modality   = strtoupper(trim($body['modality'] ?? 'X-RAY'));

        if (!$documentId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'document_id required.']);
            return;
        }

        $doc = Database::fetch("SELECT * FROM patient_documents WHERE id = ?", [$documentId]);
        if (!$doc) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Document not found.']);
            return;
        }

        // ── Simulated AI output ───────────────────────────────────────────────
        $findings = $this->simulateAiAnalysis($modality, $doc['original_filename']);
        // ─────────────────────────────────────────────────────────────────────

        // Persist run record
        Database::query(
            "INSERT INTO imaging_ai_findings (document_id, patient_id, run_by, modality, findings_json, accepted)
             VALUES (?, ?, ?, ?, ?, 0)",
            [
                $documentId,
                (int)$doc['patient_id'],
                (int)$_SESSION['user_id'],
                $modality,
                json_encode($findings),
            ]
        );
        $runId = Database::lastInsertId();

        // Mark the document as having a completed analysis
        Database::query(
            "UPDATE patient_documents SET ai_analysis_status = 'completed', ai_findings_json = ? WHERE id = ?",
            [json_encode($findings), $documentId]
        );

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            (int)$doc['patient_id'], 'Run AI Imaging Analysis (' . $modality . ')', 'Documents', $documentId
        );

        echo json_encode([
            'status'   => 'success',
            'run_id'   => $runId,
            'findings' => $findings,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/imaging/save-findings
    // Provider accepts AI findings → attaches them to the patient's active
    // clinical note (if note_id provided) and marks the run as accepted.
    // Body JSON: { run_id, note_id?, patient_id }
    // -------------------------------------------------------------------------
    public function saveFindings(array $params): void
    {
        $this->checkAccess(Roles::CLINICAL);
        header('Content-Type: application/json');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $runId     = (int)($body['run_id'] ?? 0);
        $noteId    = $body['note_id'] ? (int)$body['note_id'] : null;
        $patientId = (int)($body['patient_id'] ?? 0);

        if (!$runId || !$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'run_id and patient_id required.']);
            return;
        }

        $run = Database::fetch("SELECT * FROM imaging_ai_findings WHERE id = ?", [$runId]);
        if (!$run) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Analysis run not found.']);
            return;
        }

        // If a note is provided, append the finding as imaging result text
        if ($noteId) {
            $note = Database::fetch("SELECT id, imaging_findings FROM clinical_notes WHERE id = ?", [$noteId]);
            if ($note !== false) {
                $findings  = json_decode($run['findings_json'], true);
                $appendTxt = sprintf(
                    "\n[AI Imaging — %s] %s (%.0f%% confidence). %s",
                    $run['modality'],
                    $findings['label'] ?? '',
                    ($findings['score'] ?? 0) * 100,
                    $findings['summary'] ?? ''
                );
                Database::query(
                    "UPDATE clinical_notes SET imaging_findings = CONCAT(IFNULL(imaging_findings,''), ?) WHERE id = ?",
                    [$appendTxt, $noteId]
                );
            }
        }

        Database::query(
            "UPDATE imaging_ai_findings SET accepted = 1, saved_to_note = ? WHERE id = ?",
            [$noteId, $runId]
        );

        AuditLogger::log(
            $_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'],
            $patientId, 'Accept AI Imaging Findings', 'Documents', $runId
        );

        echo json_encode(['status' => 'success', 'message' => 'Findings saved to patient record.']);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * Simulated AI response — structured to match a real Hugging Face
     * image-classification pipeline response.  In production replace the body
     * of this function with an HTTP call to the model API.
     */
    private function simulateAiAnalysis(string $modality, string $filename): array
    {
        $pools = [
            'X-RAY' => [
                ['label' => 'No Finding',                 'score' => 0.91, 'summary' => 'No acute cardiopulmonary abnormality detected on chest radiograph.',      'explanation' => 'Your chest X-ray looks normal. The lungs appear clear and the heart size is within normal limits.', 'recommendations' => ['Routine follow-up as scheduled', 'Continue current medications', 'Notify provider if symptoms change']],
                ['label' => 'Cardiomegaly',               'score' => 0.83, 'summary' => 'Increased cardiothoracic ratio consistent with cardiomegaly.',             'explanation' => 'The image shows the heart appears slightly enlarged. This can be caused by high blood pressure, heart failure, or other cardiac conditions.', 'recommendations' => ['Echocardiogram recommended', 'Cardiology consult', 'Review current cardiac medications', 'Sodium restriction diet counselling']],
                ['label' => 'Pleural Effusion',           'score' => 0.78, 'summary' => 'Blunting of the right costophrenic angle suggests pleural effusion.',      'explanation' => 'There appears to be fluid around the lung on the right side. This needs further evaluation.', 'recommendations' => ['CT chest with contrast', 'Consider thoracentesis if large', 'Repeat CXR in 4-6 weeks after treatment']],
                ['label' => 'Pulmonary Congestion',       'score' => 0.74, 'summary' => 'Increased pulmonary vascularity consistent with pulmonary congestion.',     'explanation' => 'The pattern suggests fluid is building up in the lungs, often a sign of heart failure.', 'recommendations' => ['BNP / Pro-BNP labs', 'Diuretic therapy review', 'Daily weight monitoring', 'Low-sodium diet']],
                ['label' => 'Consolidation / Pneumonia',  'score' => 0.69, 'summary' => 'Focal opacity in left lower lobe, may represent pneumonia or atelectasis.', 'explanation' => 'There is a cloudy area in the lower part of the left lung which may indicate an infection or collapsed lung segment.', 'recommendations' => ['Sputum culture', 'Antibiotic therapy if infection confirmed', 'Follow-up CXR in 6 weeks', 'Pulmonology referral if persistent']],
            ],
            'MRI' => [
                ['label' => 'Normal Brain MRI',           'score' => 0.87, 'summary' => 'No acute intracranial abnormality detected.',                              'explanation' => 'Your brain MRI appears normal with no signs of bleeding, stroke, or tumour.', 'recommendations' => ['Routine neurological follow-up', 'Continue prescribed medications']],
                ['label' => 'White Matter Changes',       'score' => 0.75, 'summary' => 'Scattered periventricular white matter hyperintensities noted on T2/FLAIR.','explanation' => 'Small bright spots were seen in the white matter of the brain, often associated with small vessel disease or aging.', 'recommendations' => ['Vascular risk factor management', 'Blood pressure optimisation', 'Neuropsychological testing', 'Follow-up MRI in 12 months']],
                ['label' => 'Lacunar Infarct',            'score' => 0.68, 'summary' => 'Small area of signal change consistent with lacunar infarction.',          'explanation' => 'A small area of the brain shows signs of a prior mini-stroke or small vessel blockage.', 'recommendations' => ['Neurology consult', 'Antiplatelet therapy review', 'MRA brain and neck', 'Cardiac monitoring']],
            ],
            'CT' => [
                ['label' => 'No Acute Finding',           'score' => 0.88, 'summary' => 'No acute intracranial or abdominal abnormality on CT.',                    'explanation' => 'Your CT scan appears normal with no immediate concerning findings.', 'recommendations' => ['Routine follow-up', 'Continue prescribed medications']],
                ['label' => 'Pericardial Effusion',       'score' => 0.72, 'summary' => 'Small-to-moderate pericardial effusion identified on CT chest.',           'explanation' => 'Fluid is present around the heart sac. This can be caused by infection, inflammation, or other conditions.', 'recommendations' => ['Echocardiogram urgently', 'Cardiology consult same day', 'Monitor haemodynamics', 'Serial imaging']],
                ['label' => 'Coronary Calcification',     'score' => 0.79, 'summary' => 'Moderate coronary artery calcification (CAC) detected.',                   'explanation' => 'Calcium deposits were found in the coronary arteries, indicating some level of plaque buildup.', 'recommendations' => ['Statin therapy assessment', 'Lifestyle modification counselling', 'Stress test consideration', 'Cardiology referral']],
            ],
            'ULTRASOUND' => [
                ['label' => 'Normal Echo',                'score' => 0.89, 'summary' => 'Normal left ventricular size and systolic function, EF 55-60%.',           'explanation' => 'Your echocardiogram shows the heart is pumping normally with no major structural problems.', 'recommendations' => ['Routine cardiology follow-up in 12 months', 'Continue current medications']],
                ['label' => 'Reduced EF (HFrEF)',         'score' => 0.81, 'summary' => 'Reduced LV ejection fraction estimated at 35-40%, consistent with HFrEF.', 'explanation' => 'The heart muscle is not squeezing as strongly as it should. This is called heart failure with reduced ejection fraction.', 'recommendations' => ['ACE inhibitor / ARB initiation', 'Beta-blocker therapy', 'Diuretic titration', 'Cardiology urgent referral', 'ICD / CRT evaluation']],
                ['label' => 'Diastolic Dysfunction',      'score' => 0.74, 'summary' => 'Grade II diastolic dysfunction with elevated filling pressures.',          'explanation' => 'The heart is having difficulty relaxing properly to fill with blood, even though the squeeze is normal.', 'recommendations' => ['Blood pressure optimisation', 'Diuretic therapy', 'Salt and fluid restriction', 'Exercise cardiac rehabilitation']],
                ['label' => 'Mitral Regurgitation',       'score' => 0.70, 'summary' => 'Moderate mitral regurgitation identified on colour Doppler imaging.',      'explanation' => 'The mitral valve is leaking, allowing blood to flow backwards in the heart.', 'recommendations' => ['Annual echocardiography surveillance', 'Cardiothoracic surgery consultation', 'Antibiotic prophylaxis guidance', 'Activity restriction review']],
            ],
            'ECG' => [
                ['label' => 'Normal Sinus Rhythm',        'score' => 0.92, 'summary' => 'Normal sinus rhythm at 72 bpm, no acute ST-T changes.',                   'explanation' => 'Your ECG shows a normal heart rhythm with no signs of acute heart attack or dangerous arrhythmia.', 'recommendations' => ['Routine follow-up as scheduled']],
                ['label' => 'Atrial Fibrillation',        'score' => 0.85, 'summary' => 'Irregular rhythm with absent P waves consistent with atrial fibrillation.','explanation' => 'Your heart is beating irregularly due to atrial fibrillation, which increases stroke risk.', 'recommendations' => ['CHA2DS2-VASc score calculation', 'Anticoagulation assessment', 'Rate/rhythm control strategy', 'Cardiology referral', 'Thyroid function tests']],
                ['label' => 'ST-Segment Elevation',       'score' => 0.88, 'summary' => 'ST elevation in leads II, III, aVF — inferior STEMI pattern.',            'explanation' => 'This ECG pattern may indicate an active heart attack and requires immediate medical evaluation.', 'recommendations' => ['IMMEDIATE: activate STEMI protocol', 'Aspirin 325 mg PO stat', 'Cath lab activation', 'Serial troponins', 'IV access and monitoring']],
                ['label' => 'Left Bundle Branch Block',   'score' => 0.77, 'summary' => 'New left bundle branch block pattern detected.',                          'explanation' => 'There is a blockage in one of the electrical pathways of the heart.', 'recommendations' => ['Urgent cardiology review', 'Serial ECGs', 'Troponin assay', 'Echocardiogram', 'Pacemaker evaluation']],
            ],
        ];

        $modalityKey = array_key_exists($modality, $pools) ? $modality : 'X-RAY';
        $pool = $pools[$modalityKey];

        // Deterministic pick from filename hash so repeated opens return same result
        $idx = abs(crc32($filename)) % count($pool);
        $result = $pool[$idx];

        // Jitter the score slightly for realism
        $jitter = (mt_rand(-40, 40) / 1000);
        $result['score'] = min(0.99, max(0.50, $result['score'] + $jitter));

        $result['modality'] = $modality;
        $result['model']    = $this->modelName($modality);
        $result['analyzed_at'] = date('c');

        return $result;
    }

    private function modelName(string $modality): string
    {
        $map = [
            'X-RAY'      => 'Specialty-EHR/chest-xray-classifier-v2',
            'MRI'        => 'Specialty-EHR/brain-mri-classifier-v1',
            'CT'         => 'Specialty-EHR/ct-scan-detector-v1',
            'ULTRASOUND' => 'Specialty-EHR/echo-ef-analyzer-v1',
            'ECG'        => 'Specialty-EHR/ecg-rhythm-classifier-v2',
        ];
        return $map[$modality] ?? 'Specialty-EHR/medical-image-classifier-v1';
    }
}
