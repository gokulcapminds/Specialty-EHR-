<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Security\CSRFTokenManager;
use App\Security\PasswordPolicy;
use App\Security\Roles;
use App\Services\AppUrl;
use App\Services\EmailService;

class AuthController {
    /** Wrong passwords allowed before the account is locked, and how long the lock lasts (minutes). */
    private const MAX_FAILED_LOGINS = 5;
    private const LOCK_MINUTES = 15;
    /** One message for unknown user / wrong password / locked account, so the screen never reveals which usernames exist. */
    private const LOGIN_FAILED_MESSAGE = 'Invalid username or password, or the account is temporarily locked. Try again later.';
    /** A valid bcrypt hash of a random string - verified against when the username is unknown so response time doesn't reveal it. */
    private const DUMMY_HASH = '$2y$10$rhXAEs9AMkneK/BHzLB9beEs3Uei5jZa53WLb1BtFEqnvzq4EVyqG';

    public function login(): void {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        header('Content-Type: application/json');

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Username and password required.']);
            return;
        }

        // Fetch User - the login field is labeled "User Name / Email", so accept either.
        $user = Database::fetch(
            "SELECT *, (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
            [$username, $username]
        );

        $locked = $user && (int)$user['is_locked'] === 1;
        $passwordOk = password_verify((string)$password, $user ? $user['password_hash'] : self::DUMMY_HASH);

        if ($user && !$locked && $passwordOk) {
            $mustChangePassword = !empty($user['must_change_password']);

            // Admin-issued temp credentials expire if never used.
            if ($mustChangePassword && !empty($user['temp_password_expires']) && strtotime($user['temp_password_expires']) < time()) {
                AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Login Blocked - Temporary Credentials Expired', 'Authentication', $user['id']);
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Your temporary login credentials have expired. Use "Forgot password" or ask your administrator to set a new password.']);
                return;
            }

            // New session id at the moment of login, so an id planted before login (session fixation) is useless afterwards.
            session_regenerate_id(true);

            // Active specialty comes from the user's own onboarded profile, never from client input -
            // otherwise any account could render any of the 7 specialty EHRs regardless of assignment.
            $specialty = $user['specialty'] ?: 'Cardiology';

            // Setup Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            // Every role's PHI/workflow visibility (patients, appointments, encounters, billing, ...) is
            // scoped to this facility - re-read fresh on every request by AuthenticationMiddleware, same
            // as user_role, so reassigning a user's facility applies on their next request, not next login.
            $_SESSION['facility_id'] = $user['facility_id'];
            $_SESSION['active_specialty'] = $specialty;
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['last_activity'] = time();
            $_SESSION['fingerprint'] = md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
            $_SESSION['created_time'] = time();
            $_SESSION['must_change_password'] = $mustChangePassword ? 1 : 0;
            // Remembers which password this session was opened with; AuthenticationMiddleware ends the session if it changes.
            $_SESSION['pw_at'] = $user['password_changed_at'];

            // Generate CSRF Token for security
            $csrfToken = CSRFTokenManager::generateToken();

            // Success: clear the failure counter / lock, stamp the login, and upgrade an old hash if the algorithm moved on.
            Database::query("UPDATE users SET last_login = NOW(), failed_login_count = 0, locked_until = NULL WHERE id = ?", [$user['id']]);
            if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT)) {
                Database::query("UPDATE users SET password_hash = ? WHERE id = ?", [password_hash((string)$password, PASSWORD_BCRYPT), $user['id']]);
            }

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
                    'theme' => $user['theme_preference'],
                    'force_password_change' => $mustChangePassword
                ],
                'csrf_token' => $csrfToken
            ]);
            return;
        }

        // Failure (same answer whatever the reason). A real, unlocked account counts a failed attempt and is locked at the limit.
        if ($user && !$locked) {
            Database::query("UPDATE users SET failed_login_count = failed_login_count + 1 WHERE id = ?", [$user['id']]);
            $count = (int)(Database::fetch("SELECT failed_login_count AS c FROM users WHERE id = ?", [$user['id']])['c'] ?? 0);
            if ($count >= self::MAX_FAILED_LOGINS) {
                Database::query(
                    "UPDATE users SET failed_login_count = 0, locked_until = DATE_ADD(NOW(), INTERVAL " . self::LOCK_MINUTES . " MINUTE) WHERE id = ?",
                    [$user['id']]
                );
                AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Account Locked - Too Many Failed Logins (' . self::LOCK_MINUTES . ' min)', 'Authentication', $user['id']);
            } else {
                AuditLogger::log(null, $username, 'Anonymous', null, 'Login Failed - Invalid Credentials', 'Authentication');
            }
        } elseif ($user && $locked) {
            AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Login Refused - Account Locked', 'Authentication', $user['id']);
        } else {
            AuditLogger::log(null, $username, 'Anonymous', null, 'Login Failed - Invalid Credentials', 'Authentication');
        }

        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => self::LOGIN_FAILED_MESSAGE]);
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
                'specialty' => $_SESSION['active_specialty'] ?? 'Cardiology',
                'first_name' => $_SESSION['first_name'] ?? '',
                'last_name' => $_SESSION['last_name'] ?? '',
                'permissions' => Roles::currentPermissions(),
                'custom_role' => $_SESSION['custom_role_name'] ?? null,
                'force_password_change' => !empty($_SESSION['must_change_password']),
                'csrf_token' => CSRFTokenManager::generateToken()
            ]
        ]);
    }

    /**
     * Lets a logged-in user (typically one on an admin-issued temporary password)
     * set their own password. Clears must_change_password so the login/API gate lifts.
     */
    public function changePassword(): void {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $currentPassword = (string)($input['current_password'] ?? '');
        $newPassword = (string)($input['new_password'] ?? '');
        $confirmPassword = (string)($input['confirm_password'] ?? '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Current password, new password, and confirmation are all required.']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'New password and confirmation do not match.']);
            return;
        }

        $user = Database::fetch("SELECT id, username, email, password_hash FROM users WHERE id = ?", [$_SESSION['user_id']]);
        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'] ?? '', $_SESSION['user_role'] ?? '', null, 'Password Change Failed - Incorrect Current Password', 'Authentication', $_SESSION['user_id']);
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
            return;
        }

        if ($problem = PasswordPolicy::check($newPassword, (string)$user['username'], (string)($user['email'] ?? ''), $user['password_hash'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $problem]);
            return;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        Database::query(
            "UPDATE users SET password_hash = ?, must_change_password = 0, temp_password_expires = NULL, password_changed_at = NOW(), failed_login_count = 0, locked_until = NULL WHERE id = ?",
            [$hash, $user['id']]
        );

        // Every OTHER session of this user ends (AuthenticationMiddleware compares pw_at); this one carries on with a fresh id.
        $stamp = Database::fetch("SELECT password_changed_at FROM users WHERE id = ?", [$user['id']]);
        $_SESSION['pw_at'] = $stamp['password_changed_at'] ?? null;
        session_regenerate_id(true);
        unset($_SESSION['must_change_password']);

        AuditLogger::log($user['id'], $user['username'], $_SESSION['user_role'] ?? '', null, 'Password Changed', 'Authentication', $user['id']);

        echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
    }

    /**
     * Step 1 of "Forgot password" (public). Always answers the same text, whether or not the account exists, so it can't be used to
     * find out who has an account. A real, active user with an email gets a one-time link that works for 30 minutes.
     */
    public function forgotPassword(): void {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $identifier = trim((string)($input['identifier'] ?? ''));
        $generic = ['status' => 'success', 'message' => 'If that account exists and has an email address, a reset link has been sent. It works for 30 minutes.'];

        if ($identifier === '' || mb_strlen($identifier) > 150) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Enter your username or email address.']);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $user = Database::fetch("SELECT id, username, first_name, email, role FROM users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1", [$identifier, $identifier]);

        // At most 3 links per hour per account and per address, so this can't be used to flood someone's inbox.
        $recentForIp = (int)Database::fetch("SELECT COUNT(*) AS c FROM password_resets WHERE requested_ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)", [$ip])['c'];
        $recentForUser = $user ? (int)Database::fetch("SELECT COUNT(*) AS c FROM password_resets WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)", [$user['id']])['c'] : 0;

        if ($user && !empty($user['email']) && $recentForIp < 3 && $recentForUser < 3) {
            $token = bin2hex(random_bytes(32));
            Database::query("UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL", [$user['id']]);     // older links stop working
            Database::query(
                "INSERT INTO password_resets (user_id, token_hash, expires_at, requested_ip) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE), ?)",
                [$user['id'], hash('sha256', $token), $ip]
            );
            $link = htmlspecialchars(AppUrl::publicUrl('index.php') . '#reset-password?token=' . $token, ENT_QUOTES);
            $first = htmlspecialchars((string)$user['first_name'], ENT_QUOTES);
            $html = "
                <div style='font-family: Arial, sans-serif; background-color: #f8fafc; padding: 24px; color: #1e293b;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 32px;'>
                        <h2 style='color: #0284c7; margin-top: 0;'>Specialty EHR</h2>
                        <h3 style='color: #0f172a;'>Reset your password</h3>
                        <p>Hi {$first},</p>
                        <p>We received a request to reset your password. Use the button below to choose a new one. The link works once and expires in 30 minutes.</p>
                        <div style='text-align: center; margin: 28px 0;'>
                            <a href='{$link}' target='_blank' style='background-color: #0284c7; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: bold; border-radius: 8px; display: inline-block;'>Choose a new password</a>
                        </div>
                        <p style='font-size: 0.85rem; color: #64748b;'>If you did not ask for this, ignore this email - your password stays the same.</p>
                    </div>
                </div>";
            EmailService::send($user['email'], 'Reset your Specialty EHR password', $html);
            AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Password Reset Requested', 'Authentication', $user['id']);
        } elseif ($user) {
            AuditLogger::log($user['id'], $user['username'], $user['role'], null, 'Password Reset Not Sent (limit reached or no email on file)', 'Authentication', $user['id']);
        }

        echo json_encode($generic);
    }

    /** Step 2 (public): the emailed token + a new password. */
    public function resetPassword(): void {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $token = (string)($input['token'] ?? '');
        $new = (string)($input['new_password'] ?? '');
        $confirm = (string)($input['confirm_password'] ?? '');
        $invalid = 'This reset link is invalid or has expired. Request a new one from the sign-in page.';

        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $invalid]);
            return;
        }
        if ($new === '' || $new !== $confirm) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $new === '' ? 'Enter a new password.' : 'New password and confirmation do not match.']);
            return;
        }

        $row = Database::fetch(
            "SELECT pr.id AS reset_id, u.id, u.username, u.email, u.role, u.password_hash
             FROM password_resets pr JOIN users u ON u.id = pr.user_id
             WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW() AND u.is_active = 1",
            [hash('sha256', $token)]
        );
        if (!$row) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $invalid]);
            return;
        }
        if ($problem = PasswordPolicy::check($new, (string)$row['username'], (string)($row['email'] ?? ''), $row['password_hash'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $problem]);      // link stays valid so they can try again
            return;
        }

        // Claim the link first (single use even if two requests race), then change the password.
        $claimed = Database::query("UPDATE password_resets SET used_at = NOW() WHERE id = ? AND used_at IS NULL", [$row['reset_id']])->rowCount();
        if ($claimed !== 1) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $invalid]);
            return;
        }
        Database::query(
            "UPDATE users SET password_hash = ?, must_change_password = 0, temp_password_expires = NULL, password_changed_at = NOW(), failed_login_count = 0, locked_until = NULL WHERE id = ?",
            [password_hash($new, PASSWORD_BCRYPT), $row['id']]
        );
        AuditLogger::log($row['id'], $row['username'], $row['role'], null, 'Password Reset Completed', 'Authentication', $row['id']);

        echo json_encode(['status' => 'success', 'message' => 'Your password has been changed. You can sign in now.']);
    }
}
