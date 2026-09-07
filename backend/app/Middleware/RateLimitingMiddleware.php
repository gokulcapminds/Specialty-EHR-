<?php
namespace App\Middleware;

class RateLimitingMiddleware {
    public function handle(array $params): bool {
        $config = require __DIR__ . '/../../config/security.php';
        if (!$config['rate_limit']['enabled']) {
            return true;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $now = time();

        // Create temporary storage directory if not exists
        $dir = dirname(__DIR__, 3) . '/storage';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $limitFile = $dir . '/rate_limits.json';
        $data = [];
        if (file_exists($limitFile)) {
            $data = json_decode(file_get_contents($limitFile), true) ?: [];
        }

        // Clean up expired limits
        foreach ($data as $key => $timestamps) {
            $data[$key] = array_filter($timestamps, function($ts) use ($now, $config) {
                return ($now - $ts) < $config['rate_limit']['window'];
            });
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        // Add current timestamp
        $data[$ip][] = $now;

        // Check limit
        if (count($data[$ip]) > $config['rate_limit']['max_requests']) {
            file_put_contents($limitFile, json_encode($data));
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.'
            ]);
            return false;
        }

        file_put_contents($limitFile, json_encode($data));
        return true;
    }
}
