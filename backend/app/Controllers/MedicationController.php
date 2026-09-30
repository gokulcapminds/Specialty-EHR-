<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

class MedicationController {
    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
    }

    private function checkManageAccess(): bool {
        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, ['Doctor', 'Therapist', 'Super Admin'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Only a provider can manage medication status or refills.']);
            return false;
        }
        return true;
    }

    private function decoratePatientName(array &$row): void {
        $row['patient_first_name'] = !empty($row['first_name_encrypted']) ? EncryptionService::decrypt($row['first_name_encrypted']) : 'Patient';
        $row['patient_last_name'] = !empty($row['last_name_encrypted']) ? EncryptionService::decrypt($row['last_name_encrypted']) : ('#' . $row['patient_id']);
        unset($row['first_name_encrypted'], $row['last_name_encrypted']);
    }

    // GET /api/medications/workspace — cross-patient medication list
    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'Doctor';

        $where = [];
        $params = [];

        if (!in_array($userRole, ['Super Admin', 'Admin', 'Staff', 'Billing Staff', 'Receptionist'])) {
            $where[] = "(m.provider_id = ? OR m.provider_id IS NULL)";
            $params[] = $userId;
        }

        foreach (['status', 'patient_id'] as $filterKey) {
            if (!empty($_GET[$filterKey])) {
                $where[] = "m.$filterKey = ?";
                $params[] = $_GET[$filterKey];
            }
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $medications = Database::fetchAll(
            "SELECT m.*,
                    p.first_name_encrypted, p.last_name_encrypted,
                    CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM medications m
             LEFT JOIN patients p ON m.patient_id = p.id
             LEFT JOIN users u ON m.provider_id = u.id
             $whereSql
             ORDER BY m.created_at DESC, m.id DESC",
            $params
        );

        foreach ($medications as &$med) {
            $this->decoratePatientName($med);
        }

        echo json_encode(['status' => 'success', 'data' => $medications]);
    }

    // GET /api/medications/{patient_id} — one patient's medication list
    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $medications = Database::fetchAll(
            "SELECT m.*, CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM medications m
             LEFT JOIN users u ON m.provider_id = u.id
             WHERE m.patient_id = ?
             ORDER BY (m.status = 'Active') DESC, m.created_at DESC, m.id DESC",
            [$patientId]
        );

        echo json_encode(['status' => 'success', 'data' => $medications]);
    }

    // POST /api/medications — prescribe a new medication
    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientId = $input['patient_id'] ?? null;
        $providerId = $input['provider_id'] ?? null;
        $medicationName = trim($input['medication_name'] ?? '');
        $dosage = trim($input['dosage'] ?? '');
        $route = trim($input['route'] ?? '');
        $frequency = trim($input['frequency'] ?? '');
        $startDate = $input['start_date'] ?? '';

        if (empty($patientId) || empty($providerId) || empty($medicationName) || empty($dosage) || empty($route) || empty($frequency) || empty($startDate)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient, provider, medication name, dosage, route, frequency, and start date are required.']);
            return;
        }

        $rxnormCode = $input['rxnorm_code'] ?? null;
        $quantity = !empty($input['quantity']) ? intval($input['quantity']) : null;
        $refills = isset($input['refills']) && $input['refills'] !== '' ? intval($input['refills']) : 0;
        $endDate = !empty($input['end_date']) ? $input['end_date'] : null;
        $instructions = $input['instructions'] ?? null;
        $encounterId = !empty($input['encounter_id']) ? intval($input['encounter_id']) : null;

        Database::query(
            "INSERT INTO medications (patient_id, provider_id, encounter_id, medication_name, rxnorm_code, dosage, route, frequency, quantity, refills, refills_remaining, start_date, end_date, status, instructions)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active', ?)",
            [$patientId, $providerId, $encounterId, $medicationName, $rxnormCode, $dosage, $route, $frequency, $quantity, $refills, $refills, $startDate, $endDate, $instructions]
        );
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, "Medication Prescribed: {$medicationName} ({$dosage} {$route} {$frequency})", 'Medications', (string)$newId);

        echo json_encode(['status' => 'success', 'message' => 'Medication prescribed successfully.', 'id' => $newId]);
    }

    // PUT /api/medications/{id}/status — status transition (Completed, Discontinued, On-Hold, Active/Resume)
    public function updateStatus(array $params): void {
        $this->checkAccess();
        if (!$this->checkManageAccess()) return;
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? null;

        $allowed = ['Active', 'Completed', 'Discontinued', 'On-Hold'];
        if (!$id || !$status || !in_array($status, $allowed)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Medication ID and a valid status are required.']);
            return;
        }

        $med = Database::fetch("SELECT id, patient_id, medication_name, status FROM medications WHERE id = ?", [$id]);
        if (!$med) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Medication not found.']);
            return;
        }
        if (in_array($med['status'], ['Completed', 'Discontinued'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "{$med['medication_name']} is already {$med['status']} and can no longer be changed."]);
            return;
        }

        if ($status === 'Discontinued') {
            $reason = trim($input['discontinue_reason'] ?? '');
            if (empty($reason)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'A reason is required to discontinue a medication.']);
                return;
            }
            Database::query("UPDATE medications SET status = 'Discontinued', discontinue_reason = ?, updated_at = NOW() WHERE id = ?", [$reason, $id]);
        } else {
            Database::query("UPDATE medications SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $med['patient_id'], "Medication Status Changed: {$med['medication_name']} -> {$status}", 'Medications', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$med['medication_name']} marked {$status}."]);
    }

    // POST /api/medications/{id}/refill
    public function refill(array $params): void {
        $this->checkAccess();
        if (!$this->checkManageAccess()) return;
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $med = Database::fetch("SELECT id, patient_id, medication_name, status, refills_remaining FROM medications WHERE id = ?", [$id]);
        if (!$med) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Medication not found.']);
            return;
        }
        if ($med['status'] !== 'Active') {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "{$med['medication_name']} is not Active and cannot be refilled."]);
            return;
        }
        if ((int)$med['refills_remaining'] <= 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'No refills remaining — a new prescription is required.']);
            return;
        }

        Database::query("UPDATE medications SET refills_remaining = refills_remaining - 1, updated_at = NOW() WHERE id = ?", [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $med['patient_id'], "Medication Refilled: {$med['medication_name']}", 'Medications', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "{$med['medication_name']} refilled.", 'refills_remaining' => (int)$med['refills_remaining'] - 1]);
    }
}
