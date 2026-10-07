<?php
namespace App\Middleware;

use App\Models\Database;
use App\Security\RouteAreas;
use App\Security\Roles;

class AuthenticationMiddleware {
    private function unauthenticated(): bool {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'authenticated' => false,
            'message' => 'Unauthorized. Please authenticate.'
        ]);
        return false;
    }

    public function handle(array $params): bool {
        if (!isset($_SESSION['user_id'])) {
            return $this->unauthenticated();
        }

        // The session remembers who logged in, but the account may have changed since: re-read it on every request
        // (one primary-key lookup) so deactivating or demoting a user takes effect immediately, not at their next login.
        $user = Database::fetch(
            "SELECT u.role, u.is_active, u.password_changed_at, u.facility_id, u.custom_role_id, cr.name AS custom_role_name, cr.base_role AS custom_base_role, cr.permissions_matrix AS custom_matrix
             FROM users u LEFT JOIN custom_roles cr ON cr.id = u.custom_role_id WHERE u.id = ?",
            [$_SESSION['user_id']]
        );
        if (!$user || !(int)$user['is_active']) {
            session_unset();
            session_destroy();
            return $this->unauthenticated();
        }
        // The password was changed/reset after this session was opened (for example on another device): end the session.
        if (($user['password_changed_at'] ?? null) !== ($_SESSION['pw_at'] ?? null)) {
            session_unset();
            session_destroy();
            return $this->unauthenticated();
        }
        if (($user['role'] ?? '') !== ($_SESSION['user_role'] ?? null)) {
            $_SESSION['user_role'] = $user['role'];
        }
        if (($user['facility_id'] ?? null) != ($_SESSION['facility_id'] ?? null)) {
            $_SESSION['facility_id'] = $user['facility_id'];
        }

        // Custom role: the user is still their base role (scoping, specialty rules) but may only do what the role's ticks allow.
        // A user pointing at a deleted/invalid custom role gets NO access rather than silently falling back to the full base role.
        unset($_SESSION['custom_perms'], $_SESSION['custom_role_name']);
        if (!empty($user['custom_role_id'])) {
            $matrix = json_decode((string)($user['custom_matrix'] ?? ''), true);
            if (!is_array($matrix) || empty($user['custom_base_role'])) {
                $matrix = [];
            } else {
                $_SESSION['user_role'] = $user['custom_base_role'];
            }
            $_SESSION['custom_perms'] = $matrix;
            $_SESSION['custom_role_name'] = $user['custom_role_name'] ?? '';
        }

        // A user on an admin-issued temporary password can only reach /api/me (so the frontend
        // router can detect the flag and redirect) or /api/auth/change-password - everything else
        // is blocked server-side so the forced-change screen can't be bypassed by calling the API directly.
        if (!empty($_SESSION['must_change_password'])) {
            // Exact-match the path (not a substring check) so routes like /api/messages/* -
            // which merely start with the same characters as /api/me - aren't wrongly let through.
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
            $apiPos = stripos($path, '/api/');
            $routePath = $apiPos !== false ? substr($path, $apiPos) : $path;
            $allowed = in_array($routePath, ['/api/me', '/api/auth/change-password'], true);
            if (!$allowed) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'code' => 'PASSWORD_CHANGE_REQUIRED',
                    'message' => 'You must change your temporary password before continuing.'
                ]);
                return false;
            }
        }

        if (Roles::sessionMatrix() !== null && !$this->customRoleAllowsRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden: your role does not allow this.']);
            return false;
        }

        return true;
    }

    /** Custom-role users: the request's area/action (see RouteAreas) must be ticked. Unmapped routes are denied. */
    private function customRoleAllowsRequest(): bool {
        $need = RouteAreas::resolve($_SERVER['REQUEST_METHOD'] ?? 'GET', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
        if ($need === RouteAreas::NEUTRAL) return true;
        if ($need === null) return false;
        return Roles::customAllows($need['area'], $need['action']);
    }
}
