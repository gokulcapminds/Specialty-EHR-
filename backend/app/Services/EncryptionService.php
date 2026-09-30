<?php
namespace App\Services;

class EncryptionService {
    private static function getConfig(): array {
        return require __DIR__ . '/../../config/security.php';
    }

    /**
     * Encrypts plaintext data using AES-256-GCM.
     */
    public static function encrypt(string $data): string {
        $config = self::getConfig();
        $cipher = $config['encryption']['cipher'];
        $key = hash('sha256', $config['encryption']['key'], true);

        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivlen);
        $tag = '';

        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($encrypted === false) {
            throw new \RuntimeException('Encryption failed');
        }

        // Return iv + tag + ciphertext encoded in base64
        return base64_encode($iv . $tag . $encrypted);
    }

    /**
     * Deterministic HMAC of plaintext, for matching encrypted fields (e.g. duplicate-patient
     * lookup) without being able to recover the plaintext from the index itself.
     */
    public static function blindIndex(string $data): string {
        $config = self::getConfig();
        $key = hash('sha256', $config['encryption']['key'], true);
        return hash_hmac('sha256', $data, $key);
    }

    /**
     * Decrypts ciphertext data using AES-256-GCM.
     */
    public static function decrypt(string $base64Data): string {
        // Guard: return empty string immediately if there is nothing to decrypt
        if (empty(trim($base64Data))) {
            return '';
        }

        $config = self::getConfig();
        $cipher = $config['encryption']['cipher'];
        $key = hash('sha256', $config['encryption']['key'], true);

        $data = base64_decode($base64Data);
        if ($data === false || strlen($data) < 28) {
            // 12 bytes IV + 16 bytes tag = minimum 28 bytes needed
            return '';
        }

        $ivlen = openssl_cipher_iv_length($cipher);
        $taglen = 16; // Standard tag length for GCM

        $iv = substr($data, 0, $ivlen);
        $tag = substr($data, $ivlen, $taglen);
        $encrypted = substr($data, $ivlen + $taglen);

        $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($decrypted === false) {
            return '[Decryption Failed]';
        }

        return $decrypted;
    }
}
