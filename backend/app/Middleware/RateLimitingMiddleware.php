<?php
namespace App\Middleware;

class RateLimitingMiddleware {
    public function handle(array $params): bool {
        $config = require __DIR__ . '/../../config/security.php';
        if (!$config['rate_limit']['enabled']) {
            return true;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $now = time();

        // Create temporary storage directory if not exists
        $dir = dirname(__DIR__, 3) . '/storage';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Read-modify-write under an exclusive lock: parallel requests (the SPA fires several at once) used to overwrite each
        // other's entries, and a half-written file could reset every counter.
        $handle = fopen($dir . '/rate_limits.json', 'c+');
        if ($handle === false) {
            return true;      // can't track limits right now; don't lock everyone out
        }
        flock($handle, LOCK_EX);
        $raw = stream_get_contents($handle);
        $data = $raw ? (json_decode($raw, true) ?: []) : [];

        // Clean up expired limits
        foreach ($data as $key => $timestamps) {
            $data[$key] = array_values(array_filter($timestamps, function ($ts) use ($now, $config) {
                return ($now - $ts) < $config['rate_limit']['window'];
            }));
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        // Add current timestamp
        $data[$ip][] = $now;
        $tooMany = count($data[$ip]) > $config['rate_limit']['max_requests'];

        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($data));
        fflush($handle);
        flock($handle, LOCK_UN);
        fclose($handle);

        if ($tooMany) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Too many requests. Please try again later.'
            ]);
            return false;
        }
        return true;
    }
}
