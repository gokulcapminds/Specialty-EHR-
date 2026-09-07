<?php
// Security settings configuration
return [
    'session' => [
        'lifetime' => 900, // 15 minutes session inactivity timeout (900 seconds)
        'cookie_lifetime' => 0, // Session cookie ends on browser close
        'cookie_path' => '/',
        'cookie_domain' => '',
        'cookie_secure' => false, // Set to true in HTTPS environments
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
    ],
    'csrf' => [
        'token_name' => 'csrf_token',
        'header_name' => 'X-CSRF-Token',
    ],
    'rate_limit' => [
        'enabled' => true,
        'max_requests' => 100, // max requests per window
        'window' => 60, // sliding window in seconds (1 minute)
    ],
    'encryption' => [
        'cipher' => 'aes-256-gcm',
        // Key should be set securely in environment or server variables
        'key' => 'bhevariol_health_default_secret_encryption_key_32_bytes_long_!!!'
    ],
    'allowed_origins' => [
        'http://localhost',
        'http://127.0.0.1'
    ]
];
