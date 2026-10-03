<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

class AllergyController {
    private function checkAccess(): void {
        // Allergies are clinical data: clinical roles only (reception and billing never see them).
        Roles::enforce(Roles::CLINICAL);
    }

    private function decoratePatientName(array &$row): void {
        $row['patient_first_name'] = !empty($row['first_name_encrypted']) ? EncryptionService::decrypt($row['first_name_encrypted']) : 'Patient';
        $row['patient_last_name'] = !empty($row['last_name_encrypted']) ? EncryptionService::decrypt($row['last_name_encrypted']) : ('#' . $row['patient_id']);
        unset($row['first_name_encrypted'], $row['last_name_encrypted']);
    }

    // GET /api/allergies/workspace — cross-patient allergy list
    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $where = [];
        $params = [];

        foreach (['status', 'patient_id', 'category', 'severity'] as $filterKey) {
            if (!empty($_GET[$filterKey])) {
                $where[] = "a.$filterKey = ?";
                $params[] = $_GET[$filterKey];
            }
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $allergies = Database::fetchAll(
            "SELECT a.*,
                    p.first_name_encrypted, p.last_name_encrypted,
                    CONCAT(u.first_name, ' ', u.last_name) AS recorded_by_name
             FROM patient_allergies a
             LEFT JOIN patients p ON a.patient_id = p.id
             LEFT JOIN users u ON a.recorded_by = u.id
             $whereSql
             ORDER BY a.created_at DESC, a.id DESC",
            $params
        );

        foreach ($allergies as &$row) {
            $this->decoratePatientName($row);
        }

        echo json_encode(['status' => 'success', 'data' => $allergies]);
    }

    // GET /api/allergies/{patient_id} — one patient's allergy list
    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $allergies = Database::fetchAll(
            "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) AS recorded_by_name
             FROM patient_allergies a
             LEFT JOIN users u ON a.recorded_by = u.id
             WHERE a.patient_id = ?
             ORDER BY (a.status = 'Active') DESC, a.created_at DESC, a.id DESC",
            [$patientId]
        );

        echo json_encode(['status' => 'success', 'data' => $allergies]);
    }

    // POST /api/allergies — record a new allergy
    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientId = $input['patient_id'] ?? null;
        $allergen = trim($input['allergen'] ?? '');

        if (empty($patientId) || empty($allergen)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient and allergen are required.']);
            return;
        }

        $category = $input['category'] ?? 'Medication';
        $reaction = $input['reaction'] ?? null;
        $severity = $input['severity'] ?? 'Moderate';
        $onsetDate = !empty($input['onset_date']) ? $input['onset_date'] : null;
        $notes = $input['notes'] ?? null;

        Database::query(
            "INSERT INTO patient_allergies (patient_id, allergen, category, reaction, severity, onset_date, status, notes, recorded_by)
             VALUES (?, ?, ?, ?, ?, ?, 'Active', ?, ?)",
            [$patientId, $allergen, $category, $reaction, $severity, $onsetDate, $notes, $_SESSION['user_id']]
        );
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, "Allergy Recorded: {$allergen} ({$severity})", 'Allergies', (string)$newId);

        echo json_encode(['status' => 'success', 'message' => 'Allergy recorded.', 'id' => $newId]);
    }

    // PUT /api/allergies/{id} — full-field edit
    public function update(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $allergy = Database::fetch("SELECT id, patient_id, allergen FROM patient_allergies WHERE id = ?", [$id]);
        if (!$allergy) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Allergy not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $allergen = trim($input['allergen'] ?? '');
        if (empty($allergen)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Allergen is required.']);
            return;
        }

        $category = $input['category'] ?? 'Medication';
        $reaction = $input['reaction'] ?? null;
        $severity = $input['severity'] ?? 'Moderate';
        $onsetDate = !empty($input['onset_date']) ? $input['onset_date'] : null;
        $notes = $input['notes'] ?? null;

        Database::query(
            "UPDATE patient_allergies SET allergen = ?, category = ?, reaction = ?, severity = ?, onset_date = ?, notes = ?, updated_at = NOW() WHERE id = ?",
            [$allergen, $category, $reaction, $severity, $onsetDate, $notes, $id]
        );

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $allergy['patient_id'], "Allergy Updated: {$allergen}", 'Allergies', (string)$id);

        echo json_encode(['status' => 'success', 'message' => 'Allergy updated.']);
    }

    // POST /api/allergies/{id}/resolve
    public function resolve(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $allergy = Database::fetch("SELECT id, patient_id, allergen FROM patient_allergies WHERE id = ?", [$id]);
        if (!$allergy) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Allergy not found.']);
            return;
        }

        Database::query("UPDATE patient_allergies SET status = 'Resolved', updated_at = NOW() WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $allergy['patient_id'], "Allergy Resolved: {$allergy['allergen']}", 'Allergies', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$allergy['allergen']} marked Resolved."]);
    }

    // POST /api/allergies/{id}/reactivate
    public function reactivate(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $allergy = Database::fetch("SELECT id, patient_id, allergen FROM patient_allergies WHERE id = ?", [$id]);
        if (!$allergy) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Allergy not found.']);
            return;
        }

        Database::query("UPDATE patient_allergies SET status = 'Active', updated_at = NOW() WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $allergy['patient_id'], "Allergy Reactivated: {$allergy['allergen']}", 'Allergies', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$allergy['allergen']} reactivated."]);
    }
}
