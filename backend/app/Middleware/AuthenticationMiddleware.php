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
        return true;
    }
}
