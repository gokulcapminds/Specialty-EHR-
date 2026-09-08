<?php
namespace App\Middleware;

use App\Security\CSRFTokenManager;

class CSRFMiddleware {
    public function handle(array $params): bool {
        $method = $_SERVER['REQUEST_METHOD'];

        if (empty($_SESSION['csrf_token'])) {
            CSRFTokenManager::generateToken();
        }

        // Apply to modifying requests only
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;

            if (!CSRFTokenManager::validateToken($token)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'CSRF verification failed or token expired. Please try again.',
                    'new_csrf_token' => $_SESSION['csrf_token'] ?? ''
                ]);
                return false;
            }
        }
        return true;
    }
}
