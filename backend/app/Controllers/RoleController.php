<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class RoleController {
    // Must match App\Controllers\UserController::ASSIGNABLE_ROLES — the users.role ENUM vocabulary
    // that every custom role's base_role ultimately resolves down to.
    private const BASE_ROLES = ['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff'];
    private const ROLE_TYPES = ['security', 'template', 'both'];

    private function checkAdminAccess(): void {
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'Super Admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Administrator privileges required.']);
            exit();
        }
    }

    public function index(): void {
        header('Content-Type: application/json');

        $type = $_GET['type'] ?? '';
        $sql = "SELECT id, name, description, icon, base_role, permissions, permissions_matrix, role_type, is_system, created_at, updated_at FROM custom_roles";
        $params = [];
        if (in_array($type, self::ROLE_TYPES, true)) {
            // 'security' also includes 'both' rows (they ARE security roles, plus a template); same for 'template'.
            $sql .= " WHERE role_type = ? OR role_type = 'both'";
            $params[] = $type;
        }
        $sql .= " ORDER BY is_system DESC, name ASC";
        $roles = Database::fetchAll($sql, $params);

        // Decode JSON fields for easier frontend consumption
        foreach ($roles as &$r) {
            if (is_string($r['permissions'])) {
                $r['permissions'] = json_decode($r['permissions'], true) ?? [];
            }
            if (isset($r['permissions_matrix']) && is_string($r['permissions_matrix'])) {
                $r['permissions_matrix'] = json_decode($r['permissions_matrix'], true) ?? null;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $roles]);
    }

    public function store(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');
        $icon = trim($input['icon'] ?? 'fas fa-id-badge');
        $baseRole = trim($input['base_role'] ?? 'Receptionist');
        $roleType = trim($input['role_type'] ?? 'template');
        $permissions = $input['permissions'] ?? [];
        $permissionsMatrix = $input['permissions_matrix'] ?? null;

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role name is required.']);
            return;
        }

        if (!in_array($baseRole, self::BASE_ROLES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid base role.']);
            return;
        }

        if (!in_array($roleType, self::ROLE_TYPES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid role type.']);
            return;
        }

        $existing = Database::fetch("SELECT id FROM custom_roles WHERE name = ?", [$name]);
        if ($existing) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'A role with this name already exists.']);
            return;
        }

        $permissionsJson = is_array($permissions) ? json_encode($permissions) : $permissions;
        $permissionsMatrixJson = $permissionsMatrix !== null ? (is_array($permissionsMatrix) ? json_encode($permissionsMatrix) : $permissionsMatrix) : null;

        try {
            $sql = "INSERT INTO custom_roles (name, description, icon, base_role, permissions, permissions_matrix, role_type, is_system, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
            Database::query($sql, [$name, $description, $icon, $baseRole, $permissionsJson, $permissionsMatrixJson, $roleType]);
            $newId = Database::lastInsertId();

            AuditLogger::log(
                $_SESSION['user_id'] ?? null,
                $_SESSION['username'] ?? 'System',
                'Create Custom Role',
                null,
                "Created custom role: {$name}",
                'Administration',
                $newId
            );

            echo json_encode([
                'status' => 'success',
                'message' => "Custom role '{$name}' created successfully.",
                'data' => [
                    'id' => $newId,
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon,
                    'base_role' => $baseRole,
                    'role_type' => $roleType,
                    'permissions' => $permissions,
                    'permissions_matrix' => $permissionsMatrix
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('RoleController::store error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not save the custom role.']);
        }
    }

    public function update(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $roleId = $params['id'] ?? null;
        if (!$roleId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role ID required.']);
            return;
        }

        $existing = Database::fetch("SELECT * FROM custom_roles WHERE id = ?", [$roleId]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Custom role not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $isSystemRole = (int)($existing['is_system'] ?? 0) === 1;

        $name = isset($input['name']) ? trim($input['name']) : $existing['name'];
        $description = isset($input['description']) ? trim($input['description']) : $existing['description'];
        $icon = isset($input['icon']) ? trim($input['icon']) : $existing['icon'];
        $baseRole = isset($input['base_role']) ? trim($input['base_role']) : $existing['base_role'];
        $roleType = isset($input['role_type']) ? trim($input['role_type']) : ($existing['role_type'] ?? 'template');
        $permissions = isset($input['permissions']) ? $input['permissions'] : (json_decode($existing['permissions'], true) ?? []);
        $permissionsMatrix = isset($input['permissions_matrix']) ? $input['permissions_matrix'] : (json_decode($existing['permissions_matrix'] ?? 'null', true));

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role name cannot be empty.']);
            return;
        }

        // Built-in security roles are load-bearing (matched by name elsewhere) — identity can't be edited, only permissions.
        if ($isSystemRole && ($name !== $existing['name'] || $baseRole !== $existing['base_role'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Built-in role name and base role cannot be changed.']);
            return;
        }

        if (!in_array($baseRole, self::BASE_ROLES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid base role.']);
            return;
        }

        if (!in_array($roleType, self::ROLE_TYPES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid role type.']);
            return;
        }

        // Prevent renaming to an existing role name
        $nameConflict = Database::fetch("SELECT id FROM custom_roles WHERE name = ? AND id != ?", [$name, $roleId]);
        if ($nameConflict) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Another role with this name already exists.']);
            return;
        }

        $permissionsJson = is_array($permissions) ? json_encode($permissions) : $permissions;
        $permissionsMatrixJson = $permissionsMatrix !== null ? (is_array($permissionsMatrix) ? json_encode($permissionsMatrix) : $permissionsMatrix) : null;

        try {
            $sql = "UPDATE custom_roles SET name = ?, description = ?, icon = ?, base_role = ?, permissions = ?, permissions_matrix = ?, role_type = ?, updated_at = NOW() WHERE id = ?";
            Database::query($sql, [$name, $description, $icon, $baseRole, $permissionsJson, $permissionsMatrixJson, $roleType, $roleId]);

            AuditLogger::log(
                $_SESSION['user_id'] ?? null,
                $_SESSION['username'] ?? 'System',
                'Update Custom Role',
                null,
                "Updated custom role: {$name}",
                'Administration',
                $roleId
            );

            echo json_encode([
                'status' => 'success',
                'message' => "Custom role '{$name}' updated successfully.",
                'data' => [
                    'id' => $roleId,
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon,
                    'base_role' => $baseRole,
                    'role_type' => $roleType,
                    'permissions' => $permissions,
                    'permissions_matrix' => $permissionsMatrix
                ]
            ]);
        } catch (\Throwable $e) {
            error_log('RoleController::update error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not update the custom role.']);
        }
    }

    public function delete(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $roleId = $params['id'] ?? null;
        if (!$roleId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role ID required.']);
            return;
        }

        $existing = Database::fetch("SELECT * FROM custom_roles WHERE id = ?", [$roleId]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Custom role not found.']);
            return;
        }

        if ((int)$existing['is_system'] === 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'System default role templates cannot be deleted.']);
            return;
        }

        try {
            Database::query("DELETE FROM custom_roles WHERE id = ?", [$roleId]);

            AuditLogger::log(
                $_SESSION['user_id'] ?? null,
                $_SESSION['username'] ?? 'System',
                'Delete Custom Role',
                null,
                "Deleted custom role: {$existing['name']}",
                'Administration',
                $roleId
            );

            echo json_encode(['status' => 'success', 'message' => "Custom role '{$existing['name']}' deleted successfully."]);
        } catch (\Throwable $e) {
            error_log('RoleController::delete error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Could not delete the custom role.']);
        }
    }
}
