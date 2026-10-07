<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

class ProblemController {
    private function checkAccess(): void {
        // The problem list / diagnoses are clinical data: clinical roles only.
        Roles::enforce(Roles::CLINICAL);
    }

    private function decoratePatientName(array &$row): void {
        $row['patient_first_name'] = !empty($row['first_name_encrypted']) ? EncryptionService::decrypt($row['first_name_encrypted']) : 'Patient';
        $row['patient_last_name'] = !empty($row['last_name_encrypted']) ? EncryptionService::decrypt($row['last_name_encrypted']) : ('#' . $row['patient_id']);
        unset($row['first_name_encrypted'], $row['last_name_encrypted']);
    }

    // GET /api/problems/workspace — cross-patient problem list
    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $where = [];
        $params = [];

        foreach (['status', 'patient_id', 'icd10_code'] as $filterKey) {
            if (!empty($_GET[$filterKey])) {
                $where[] = "pr.$filterKey = ?";
                $params[] = $_GET[$filterKey];
            }
        }

        $where[] = "p.facility_id = ?";
        $params[] = $_SESSION['facility_id'] ?? null;
        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $problems = Database::fetchAll(
            "SELECT pr.*,
                    p.first_name_encrypted, p.last_name_encrypted,
                    CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
             FROM patient_problems pr
             LEFT JOIN patients p ON pr.patient_id = p.id
             LEFT JOIN users u ON pr.created_by = u.id
             $whereSql
             ORDER BY pr.created_at DESC, pr.id DESC",
            $params
        );

        foreach ($problems as &$row) {
            $this->decoratePatientName($row);
        }

        echo json_encode(['status' => 'success', 'data' => $problems]);
    }

    // GET /api/problems/{patient_id} — one patient's problem list
    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }
        if (!Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $problems = Database::fetchAll(
            "SELECT pr.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
             FROM patient_problems pr
             LEFT JOIN users u ON pr.created_by = u.id
             WHERE pr.patient_id = ?
             ORDER BY (pr.status = 'Active') DESC, (pr.status = 'Chronic') DESC, pr.created_at DESC, pr.id DESC",
            [$patientId]
        );

        echo json_encode(['status' => 'success', 'data' => $problems]);
    }

    // POST /api/problems — add a new diagnosis to the problem list
    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientId = $input['patient_id'] ?? null;
        $description = trim($input['description'] ?? '');

        if (empty($patientId) || empty($description)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient and diagnosis description are required.']);
            return;
        }
        if (!Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $_SESSION['facility_id'] ?? null])) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient not found.']);
            return;
        }

        $icd10Code = $input['icd10_code'] ?? null;
        $onsetDate = !empty($input['onset_date']) ? $input['onset_date'] : null;
        $chronicity = $input['chronicity'] ?? 'Chronic';
        $clinicalNotes = $input['clinical_notes'] ?? null;

        Database::query(
            "INSERT INTO patient_problems (patient_id, icd10_code, description, onset_date, status, chronicity, clinical_notes, created_by)
             VALUES (?, ?, ?, ?, 'Active', ?, ?, ?)",
            [$patientId, $icd10Code, $description, $onsetDate, $chronicity, $clinicalNotes, $_SESSION['user_id']]
        );
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, "Diagnosis Added: {$description}" . ($icd10Code ? " ({$icd10Code})" : ''), 'Diagnoses', (string)$newId);

        echo json_encode(['status' => 'success', 'message' => 'Diagnosis added to problem list.', 'id' => $newId]);
    }

    // PUT /api/problems/{id} — full-field edit
    public function update(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $problem = Database::fetch(
            "SELECT pr.id, pr.patient_id, pr.description FROM patient_problems pr JOIN patients p ON p.id = pr.patient_id WHERE pr.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$problem) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Diagnosis not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $description = trim($input['description'] ?? '');
        if (empty($description)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Diagnosis description is required.']);
            return;
        }

        $icd10Code = $input['icd10_code'] ?? null;
        $onsetDate = !empty($input['onset_date']) ? $input['onset_date'] : null;
        $chronicity = $input['chronicity'] ?? 'Chronic';
        $clinicalNotes = $input['clinical_notes'] ?? null;

        Database::query(
            "UPDATE patient_problems SET description = ?, icd10_code = ?, onset_date = ?, chronicity = ?, clinical_notes = ?, updated_at = NOW() WHERE id = ?",
            [$description, $icd10Code, $onsetDate, $chronicity, $clinicalNotes, $id]
        );

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $problem['patient_id'], "Diagnosis Updated: {$description}", 'Diagnoses', (string)$id);

        echo json_encode(['status' => 'success', 'message' => 'Diagnosis updated.']);
    }

    // POST /api/problems/{id}/resolve
    public function resolve(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $problem = Database::fetch(
            "SELECT pr.id, pr.patient_id, pr.description FROM patient_problems pr JOIN patients p ON p.id = pr.patient_id WHERE pr.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$problem) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Diagnosis not found.']);
            return;
        }

        Database::query("UPDATE patient_problems SET status = 'Resolved', resolved_date = CURDATE(), updated_at = NOW() WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $problem['patient_id'], "Diagnosis Resolved: {$problem['description']}", 'Diagnoses', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$problem['description']} marked Resolved."]);
    }

    // POST /api/problems/{id}/reactivate
    public function reactivate(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $problem = Database::fetch(
            "SELECT pr.id, pr.patient_id, pr.description FROM patient_problems pr JOIN patients p ON p.id = pr.patient_id WHERE pr.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$problem) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Diagnosis not found.']);
            return;
        }

        Database::query("UPDATE patient_problems SET status = 'Active', resolved_date = NULL, updated_at = NOW() WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $problem['patient_id'], "Diagnosis Reactivated: {$problem['description']}", 'Diagnoses', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$problem['description']} reactivated."]);
    }
}
