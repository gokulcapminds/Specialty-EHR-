<?php
namespace App\Middleware;

class AuthenticationMiddleware {
    public function handle(array $params): bool {
        if (!isset($_SESSION['user_id'])) {
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'authenticated' => false,
                'message' => 'Unauthorized. Please authenticate.'
            ]);
            return false;
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

        return true;
    }
}
