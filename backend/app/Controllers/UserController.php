<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class UserController {
    private function checkAdminAccess(): void {
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['Super Admin', 'Doctor', 'Admin'])) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'User Management permission required.']);
            exit();
        }
    }

    public function index(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $users = Database::fetchAll("SELECT id, username, first_name, last_name, role, specialty, is_active, last_login, created_at FROM users ORDER BY username ASC");
        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    public function store(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $first = trim($input['first_name'] ?? '');
        $last = trim($input['last_name'] ?? '');
        $role = $input['role'] ?? 'Therapist';
        $specialty = trim($input['specialty'] ?? 'Primary Care');

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password are required.']);
            return;
        }

        if (empty($first)) {
            $first = $username;
        }
        if (empty($last)) {
            $last = 'Provider';
        }

        // Check if username exists
        $exists = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($exists) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username already exists.']);
            return;
        }

        $email = trim($input['email'] ?? ($username . '@bhevariol.health'));

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (username, password_hash, first_name, last_name, email, role, specialty, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        Database::query($sql, [$username, $hash, $first, $last, $email, $role, $specialty]);

        AuditLogger::log($_SESSION['user_id'] ?? 1, $_SESSION['username'] ?? 'admin', $_SESSION['user_role'] ?? 'Super Admin', null, 'Create User: ' . $username, 'Administration');

        echo json_encode(['status' => 'success', 'message' => 'User created successfully.']);
    }

    public function update(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $userId = $params['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID required.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = trim($input['username'] ?? '');
        $first = trim($input['first_name'] ?? '');
        $last = trim($input['last_name'] ?? '');
        $role = $input['role'] ?? 'Therapist';
        $specialty = trim($input['specialty'] ?? 'Primary Care');
        $isActive = isset($input['is_active']) ? intval($input['is_active']) : 1;
        $newPassword = $input['password'] ?? '';

        if (empty($username)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username is required.']);
            return;
        }

        if (!empty($newPassword)) {
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET username = ?, password_hash = ?, first_name = ?, last_name = ?, role = ?, specialty = ?, is_active = ? WHERE id = ?";
            Database::query($sql, [$username, $hash, $first, $last, $role, $specialty, $isActive, $userId]);
        } else {
            $sql = "UPDATE users SET username = ?, first_name = ?, last_name = ?, role = ?, specialty = ?, is_active = ? WHERE id = ?";
            Database::query($sql, [$username, $first, $last, $role, $specialty, $isActive, $userId]);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Update User ID: ' . $userId, 'Administration');

        echo json_encode(['status' => 'success', 'message' => 'User updated successfully.']);
    }

    public function delete(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $userId = $params['id'] ?? null;
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'User ID required.']);
            return;
        }

        if (intval($userId) === intval($_SESSION['user_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Cannot delete your own active session user.']);
            return;
        }

        Database::query("DELETE FROM users WHERE id = ?", [$userId]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Delete User ID: ' . $userId, 'Administration');

        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    }

    public function getRbacPolicies(): void {
        header('Content-Type: application/json');
        $policies = Database::fetchAll("SELECT * FROM rbac_policies ORDER BY role ASC");
        echo json_encode(['status' => 'success', 'data' => $policies]);
    }

    public function updateRbacPolicy(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $role = trim($input['role'] ?? '');
        $encounter = $input['encounter_access'] ?? 'Full Access';
        $demographics = $input['demographics_access'] ?? 'Full Access';
        $billing = $input['billing_access'] ?? 'Full Access';
        $audit = $input['audit_access'] ?? 'Full Access';

        if (empty($role)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Role is required.']);
            return;
        }

        $sql = "INSERT INTO rbac_policies (role, encounter_access, demographics_access, billing_access, audit_access) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE encounter_access = VALUES(encounter_access), demographics_access = VALUES(demographics_access), billing_access = VALUES(billing_access), audit_access = VALUES(audit_access)";
        Database::query($sql, [$role, $encounter, $demographics, $billing, $audit]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Update RBAC Policy for role: ' . $role, 'Administration');

        echo json_encode(['status' => 'success', 'message' => 'RBAC Policy updated successfully.']);
    }
}
