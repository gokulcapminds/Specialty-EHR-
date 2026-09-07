<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Security\CSRFTokenManager;

class AuthController {
    public function login(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $specialty = trim($input['specialty'] ?? 'Primary Care / Family Medicine');
        if (empty($specialty)) {
            $specialty = 'Primary Care / Family Medicine';
        }

        header('Content-Type: application/json');

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password required.']);
            return;
        }

        // Fetch User
        $user = Database::fetch("SELECT * FROM users WHERE username = ? AND is_active = 1", [$username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Setup Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['active_specialty'] = $specialty;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['fingerprint'] = md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $_SESSION['created_time'] = time();

            // Generate CSRF Token for security
            $csrfToken = CSRFTokenManager::generateToken();

            // Update user last login
            Database::query("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

            // HIPAA Audit log login action
            AuditLogger::log($user['id'], $user['username'], $user['role'], null, "Login Successful (Specialty: {$specialty})", 'Authentication', $user['id']);

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful.',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'specialty' => $specialty,
                    'theme' => $user['theme_preference']
                ],
                'csrf_token' => $csrfToken
            ]);
        } else {
            // HIPAA Audit log failed login attempt
            AuditLogger::log(null, $username, 'Anonymous', null, 'Login Failed - Invalid Credentials', 'Authentication');

            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    }

    public function logout(): void {
        header('Content-Type: application/json');
        if (isset($_SESSION['user_id'])) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Logout', 'Authentication', $_SESSION['user_id']);
        }

        session_unset();
        session_destroy();

        echo json_encode(['status' => 'success', 'message' => 'Logged out successfully.']);
    }

    public function me(): void {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated.']);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['user_role'],
                'specialty' => $_SESSION['active_specialty'] ?? 'Primary Care / Family Medicine',
                'first_name' => $_SESSION['first_name'] ?? '',
                'last_name' => $_SESSION['last_name'] ?? '',
                'csrf_token' => CSRFTokenManager::generateToken()
            ]
        ]);
    }
}
