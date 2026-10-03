<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;

/**
 * The 5 built-in roles are fixed (App\Security\Roles). Super Admin may add CUSTOM roles on top: a base role (the ceiling)
 * plus per-area View/Create/Edit/Delete ticks. A user on a custom role can do what the base role allows AND the role ticks
 * (enforced in AuthenticationMiddleware via RouteAreas), so a custom role can only narrow, never widen.
 */
class RoleController {
    private function fail(int $code, string $message): never {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit();
    }

    private function audit(string $action, $id): void {
        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, $action, 'Administration', (string)$id);
    }

    /** @return array{name:string,description:string,base_role:string,matrix:array} */
    private function readInput(?int $ignoreId): array {
        $in = json_decode(file_get_contents('php://input'), true);
        if (!is_array($in)) $this->fail(400, 'Invalid request.');
        $name = trim((string)($in['name'] ?? ''));
        $description = trim((string)($in['description'] ?? ''));
        $base = (string)($in['base_role'] ?? '');
        if ($name === '' || mb_strlen($name) > 100) $this->fail(400, 'Role name is required (max 100 characters).');
        if (mb_strlen($description) > 255) $this->fail(400, 'Description is too long (max 255 characters).');
        if (!in_array($base, Roles::CUSTOM_BASE_ROLES, true)) $this->fail(400, 'Choose a base role: ' . implode(', ', Roles::CUSTOM_BASE_ROLES) . '.');
        foreach (Roles::ALL_STAFF as $builtIn) {
            if (strcasecmp($name, $builtIn) === 0) $this->fail(409, "\"$name\" is a built-in role name. Choose another name.");
        }
        $dup = Database::fetch("SELECT id FROM custom_roles WHERE name = ? AND id <> ?", [$name, (int)$ignoreId]);
        if ($dup) $this->fail(409, "A custom role named \"$name\" already exists.");
        $matrix = Roles::sanitizeMatrix($in['permissions'] ?? [], $base);
        $any = false;
        foreach ($matrix as $cell) { if (in_array(true, $cell, true)) { $any = true; break; } }
        if (!$any) $this->fail(400, 'Tick at least one permission for this role.');
        return ['name' => $name, 'description' => $description, 'base_role' => $base, 'matrix' => $matrix];
    }

    public function matrix(): void {
        Roles::enforce(Roles::ADMIN);
        header('Content-Type: application/json');
        $data = Roles::matrix();
        $rows = Database::fetchAll(
            "SELECT cr.id, cr.name, cr.description, cr.base_role, cr.permissions_matrix,
                    (SELECT COUNT(*) FROM users u WHERE u.custom_role_id = cr.id) AS user_count
             FROM custom_roles cr ORDER BY cr.name"
        );
        $data['custom_roles'] = array_map(function ($r) {
            $perm = json_decode((string)$r['permissions_matrix'], true);
            return [
                'id' => (int)$r['id'], 'name' => $r['name'], 'description' => $r['description'] ?? '', 'base_role' => $r['base_role'],
                'permissions' => Roles::sanitizeMatrix($perm, $r['base_role']), 'user_count' => (int)$r['user_count'],
            ];
        }, $rows);
        echo json_encode(['status' => 'success', 'data' => $data]);
    }

    /** Names only, for the user wizard's role select (Super Admin only, like the rest of user management). */
    public function options(): void {
        Roles::enforce(Roles::ADMIN);
        header('Content-Type: application/json');
        $rows = Database::fetchAll("SELECT id, name, base_role FROM custom_roles ORDER BY name");
        echo json_encode(['status' => 'success', 'data' => $rows]);
    }

    public function store(): void {
        Roles::enforce(Roles::ADMIN);
        header('Content-Type: application/json');
        $r = $this->readInput(null);
        Database::query(
            "INSERT INTO custom_roles (name, description, base_role, permissions_matrix) VALUES (?, ?, ?, ?)",
            [$r['name'], $r['description'], $r['base_role'], json_encode($r['matrix'])]
        );
        $id = Database::lastInsertId();
        $this->audit('Create Custom Role: ' . $r['name'], $id);
        echo json_encode(['status' => 'success', 'message' => 'Custom role created.', 'id' => (int)$id]);
    }

    public function update(array $params): void {
        Roles::enforce(Roles::ADMIN);
        header('Content-Type: application/json');
        $id = (int)($params['id'] ?? 0);
        $existing = Database::fetch("SELECT id, base_role FROM custom_roles WHERE id = ?", [$id]);
        if (!$existing) $this->fail(404, 'Custom role not found.');
        $r = $this->readInput($id);
        // Users on this role carry its base role in users.role; changing the base would silently change their scoping, so refuse while assigned.
        if ($r['base_role'] !== $existing['base_role']) {
            $used = (int)Database::fetch("SELECT COUNT(*) AS c FROM users WHERE custom_role_id = ?", [$id])['c'];
            if ($used > 0) $this->fail(409, "$used user(s) use this role. Reassign them before changing its base role.");
        }
        Database::query(
            "UPDATE custom_roles SET name = ?, description = ?, base_role = ?, permissions_matrix = ? WHERE id = ?",
            [$r['name'], $r['description'], $r['base_role'], json_encode($r['matrix']), $id]
        );
        $this->audit('Update Custom Role: ' . $r['name'], $id);
        echo json_encode(['status' => 'success', 'message' => 'Custom role saved.']);
    }

    public function delete(array $params): void {
        Roles::enforce(Roles::ADMIN);
        header('Content-Type: application/json');
        $id = (int)($params['id'] ?? 0);
        $existing = Database::fetch("SELECT id, name FROM custom_roles WHERE id = ?", [$id]);
        if (!$existing) $this->fail(404, 'Custom role not found.');
        $used = (int)Database::fetch("SELECT COUNT(*) AS c FROM users WHERE custom_role_id = ?", [$id])['c'];
        if ($used > 0) $this->fail(409, "$used user(s) use this role. Reassign them before deleting it.");
        Database::query("DELETE FROM custom_roles WHERE id = ?", [$id]);
        $this->audit('Delete Custom Role: ' . $existing['name'], $id);
        echo json_encode(['status' => 'success', 'message' => 'Custom role deleted.']);
    }
}
