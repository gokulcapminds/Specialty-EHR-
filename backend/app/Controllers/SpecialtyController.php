<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class SpecialtyController {
    private function checkAdminAccess(): void {
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'Super Admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Administrator privileges required.']);
            exit();
        }
    }

    // GET /api/specialties — list all specialties
    public function index(): void {
        header('Content-Type: application/json');

        try {
            $specialties = Database::fetchAll("
                SELECT
                    s.*,
                    (SELECT COUNT(*) FROM users u WHERE u.specialty = s.specialty_name OR u.specialty = s.specialty_key) AS provider_count
                FROM specialties s
                ORDER BY s.specialty_name ASC
            ");

            echo json_encode([
                'status' => 'success',
                'data'   => $specialties ?: []
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Failed to load specialties: ' . $e->getMessage()
            ]);
        }
    }

    // GET /api/specialties/{id} — single specialty details
    public function show(array $params): void {
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty ID required.']);
            return;
        }

        try {
            $specialty = Database::fetch("SELECT * FROM specialties WHERE id = ?", [$id]);
            if (!$specialty) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Specialty not found.']);
                return;
            }

            echo json_encode([
                'status' => 'success',
                'data'   => $specialty
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // POST /api/specialties — create new specialty
    public function store(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $key = trim($input['specialty_key'] ?? '');
        $name = trim($input['specialty_name'] ?? '');

        if (empty($key) || empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty Key and Name are required.']);
            return;
        }

        try {
            $exists = Database::fetch("SELECT id FROM specialties WHERE specialty_key = ?", [$key]);
            if ($exists) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => "Specialty with key '{$key}' already exists."]);
                return;
            }

            $code = trim($input['code'] ?? '') ?: 'SPEC-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $key), 0, 4));
            $icon = trim($input['icon'] ?? 'fas fa-stethoscope');
            $desc = trim($input['description'] ?? '');
            $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

            Database::query("
                INSERT INTO specialties (specialty_key, specialty_name, code, icon, description, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [$key, $name, $code, $icon, $desc, $isActive]);

            $newId = Database::lastInsertId();

            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'SPECIALTY_CREATED', "Created specialty: {$name} ({$key})");
            }

            echo json_encode([
                'status'  => 'success',
                'message' => 'Specialty created successfully.',
                'id'      => $newId
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create specialty: ' . $e->getMessage()]);
        }
    }

    // PUT /api/specialties/{id} — update specialty
    public function update(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty ID required.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['specialty_name'] ?? '');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty Name is required.']);
            return;
        }

        try {
            $code = trim($input['code'] ?? '');
            $icon = trim($input['icon'] ?? 'fas fa-stethoscope');
            $desc = trim($input['description'] ?? '');
            $isActive = isset($input['is_active']) ? (int)$input['is_active'] : 1;

            Database::query("
                UPDATE specialties SET
                    specialty_name = ?, code = ?, icon = ?, description = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [$name, $code, $icon, $desc, $isActive, $id]);

            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'SPECIALTY_UPDATED', "Updated specialty ID: {$id}");
            }

            echo json_encode([
                'status'  => 'success',
                'message' => 'Specialty updated successfully.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update specialty: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/specialties/{id} — delete specialty
    public function delete(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Specialty ID required.']);
            return;
        }

        try {
            $spec = Database::fetch("SELECT * FROM specialties WHERE id = ?", [$id]);
            if (!$spec) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Specialty not found.']);
                return;
            }

            $provCount = Database::fetch("SELECT COUNT(*) AS cnt FROM users WHERE specialty = ? OR specialty = ?", [$spec['specialty_key'], $spec['specialty_name']]);
            if ((int)$provCount['cnt'] > 0) {
                http_response_code(409);
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Cannot delete specialty: {$provCount['cnt']} provider(s) are assigned to it. Reassign providers first."
                ]);
                return;
            }

            $facCount = Database::fetch("SELECT COUNT(*) AS cnt FROM facility_specialties WHERE specialty_id = ?", [$id]);
            if ((int)$facCount['cnt'] > 0) {
                http_response_code(409);
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Cannot delete specialty: {$facCount['cnt']} facility(ies) still have it enabled. Remove it from those facilities first."
                ]);
                return;
            }

            Database::query("DELETE FROM specialties WHERE id = ?", [$id]);

            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, 'SPECIALTY_DELETED', "Deleted specialty: {$spec['specialty_key']}");
            }

            echo json_encode([
                'status'  => 'success',
                'message' => 'Specialty deleted successfully.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete specialty: ' . $e->getMessage()]);
        }
    }
}
