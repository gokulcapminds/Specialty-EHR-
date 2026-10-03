<?php
namespace App\Security;

/**
 * The one password rule used everywhere a password is chosen (change, reset, admin-set). The login form never applies it
 * (existing passwords stay valid until they are changed). The wizard mirrors the length / letter / number part in app.js.
 */
class PasswordPolicy {
    public const MIN_LENGTH = 10;

    private const COMMON = [
        'password', 'password1', 'password12', 'password123', 'password1234', 'passw0rd', 'p@ssw0rd', 'p@ssword1', 'qwerty', 'qwerty123', 'qwertyuiop',
        'letmein', 'letmein123', 'welcome', 'welcome1', 'welcome123', 'admin', 'admin123', 'administrator', 'iloveyou', 'monkey', 'dragon', 'football',
        'baseball', 'abc123', 'abcd1234', 'abcdef123', '111111', '123456', '1234567', '12345678', '123456789', '1234567890', '0987654321', '000000',
        'changeme', 'changeme1', 'default', 'secret', 'trustno1', 'sunshine', 'master', 'shadow', 'superman', 'hello123', 'login', 'princess', 'starwars',
        'doctor', 'doctor123', 'nurse123', 'hospital', 'hospital1', 'clinic123', 'healthcare', 'health123', 'cardiology', 'specialty', 'temp1234', 'temporary1',
    ];

    /** @return string|null a plain-English reason the password is not acceptable, or null when it is fine */
    public static function check(string $password, string $username = '', string $email = '', ?string $currentHash = null): ?string {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            return 'Password must be at least ' . self::MIN_LENGTH . ' characters.';
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return 'Password must contain at least one letter and one number.';
        }
        $lower = mb_strtolower($password);
        $normalised = preg_replace('/[^a-z0-9]/', '', $lower);
        foreach (self::COMMON as $bad) {
            if ($lower === $bad || $normalised === preg_replace('/[^a-z0-9]/', '', $bad)) {
                return 'That password is too common. Choose something less guessable.';
            }
        }
        $username = mb_strtolower(trim($username));
        if (mb_strlen($username) >= 3 && strpos($lower, $username) !== false) {
            return 'Password must not contain your username.';
        }
        $local = mb_strtolower(trim(strstr($email, '@', true) ?: ''));
        if (mb_strlen($local) >= 3 && strpos($lower, $local) !== false) {
            return 'Password must not contain the first part of your email address.';
        }
        if ($currentHash !== null && $currentHash !== '' && password_verify($password, $currentHash)) {
            return 'New password must be different from the current password.';
        }
        return null;
    }
}
