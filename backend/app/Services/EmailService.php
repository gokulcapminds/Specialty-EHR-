<?php
namespace App\Services;

class EmailService {

    // Set by send()/sendSmtp() on failure so callers (like the Settings "Send Test Email"
    // button) can surface *why* delivery failed instead of a generic true/false.
    private static ?string $lastError = null;

    public static function getLastError(): ?string {
        return self::$lastError;
    }

    // Whether real SMTP credentials are configured (DB or env) rather than relying on the
    // unauthenticated local `mail()` fallback, which most real mail providers reject/spam-filter.
    public static function isSmtpConfigured(): bool {
        $config = self::resolveConfig();
        return !empty($config['smtp_user']) && !empty($config['smtp_pass']);
    }

    private static function resolveConfig(): array {
        $dbSettings = [];
        try {
            $rows = \App\Models\Database::fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'smtp_%'");
            foreach ($rows as $r) {
                $dbSettings[$r['setting_key']] = $r['setting_value'];
            }
        } catch (\Throwable $t) {}

        return [
            'smtp_host'   => !empty($dbSettings['smtp_host']) ? trim($dbSettings['smtp_host']) : (getenv('SMTP_HOST') ?: 'smtp.gmail.com'),
            'smtp_port'   => !empty($dbSettings['smtp_port']) ? intval($dbSettings['smtp_port']) : intval(getenv('SMTP_PORT') ?: 465),
            'smtp_user'   => !empty($dbSettings['smtp_user']) ? trim($dbSettings['smtp_user']) : (getenv('SMTP_USER') ?: ''),
            'smtp_pass'   => !empty($dbSettings['smtp_pass']) ? trim($dbSettings['smtp_pass']) : (getenv('SMTP_PASS') ?: ''),
            'smtp_secure' => !empty($dbSettings['smtp_secure']) ? trim($dbSettings['smtp_secure']) : (getenv('SMTP_SECURE') ?: 'ssl'),
            'smtp_from'   => !empty($dbSettings['smtp_from']) ? trim($dbSettings['smtp_from']) : (getenv('SMTP_FROM') ?: ''),
        ];
    }

    /**
     * Send email via SMTP socket, falling back to PHP's local mail() only when no SMTP
     * credentials are configured at all (kept only so an admin who hasn't set up SMTP yet
     * doesn't lose outbound mail entirely - see isSmtpConfigured() to detect that state).
     */
    public static function send(string $toEmail, string $subject, string $bodyHtml, string $fromName = 'Specialty EHR'): bool {
        self::$lastError = null;
        $toEmail = trim($toEmail);
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            self::$lastError = "Invalid recipient email address '{$toEmail}'.";
            error_log("EHR EmailService: " . self::$lastError);
            return false;
        }

        $config = self::resolveConfig();
        $smtpHost = $config['smtp_host'];
        $smtpPort = $config['smtp_port'];
        $smtpUser = $config['smtp_user'];
        $smtpPass = $config['smtp_pass'];
        $smtpSecure = $config['smtp_secure'];
        $fromEmail = $config['smtp_from'] ?: $smtpUser;

        // If SMTP credentials are provided, attempt socket SMTP connection
        if (!empty($smtpUser) && !empty($smtpPass)) {
            try {
                $sent = self::sendSmtp($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure, $fromEmail, $fromName, $toEmail, $subject, $bodyHtml);
                if ($sent) return true;
                error_log("EHR EmailService: SMTP transport failed for user {$smtpUser} when sending to {$toEmail}: " . self::$lastError);
                return false;
            } catch (\Throwable $e) {
                self::$lastError = $e->getMessage();
                error_log("EHR EmailService SMTP Exception: " . self::$lastError);
                return false;
            }
        }

        // No SMTP configured - fall back to local mail() (unauthenticated; commonly
        // filtered as spam or blocked outright by mail providers).
        self::$lastError = 'No SMTP credentials configured; used unauthenticated local mail() fallback.';
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $sent = @mail($toEmail, $subject, $bodyHtml, $headers);
        if ($sent) self::$lastError = null;
        return $sent;
    }

    /**
     * Native Socket SMTP Transport Implementation with Multipart/Alternative Anti-Spam Construction
     */
    private static function sendSmtp(
        string $host,
        int $port,
        string $user,
        string $pass,
        string $secure,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $bodyHtml
    ): bool {
        $transport = ($secure === 'ssl') ? "ssl://{$host}" : $host;
        $socket = @fsockopen($transport, $port, $errno, $errstr, 10);
        if (!$socket) {
            self::$lastError = "Could not connect to {$host}:{$port} ({$errno} {$errstr}).";
            error_log("EHR EmailService: " . self::$lastError);
            return false;
        }
        stream_set_timeout($socket, 10);

        $read = function() use ($socket) {
            $res = "";
            while ($str = fgets($socket, 512)) {
                if ($str === false) break;
                $res .= $str;
                if (substr($str, 3, 1) === " ") break;
            }
            return $res;
        };

        $write = function($cmd) use ($socket) {
            fputs($socket, $cmd . "\r\n");
        };

        $banner = $read();
        if (empty($banner)) {
            self::$lastError = "No greeting banner received from {$host}:{$port}.";
            fclose($socket);
            return false;
        }

        $clientHost = gethostname() ?: 'localhost';
        $write("EHLO " . $clientHost);
        $read();

        if ($secure === 'tls') {
            $write("STARTTLS");
            $res = $read();
            if (strpos($res, '220') === false) {
                self::$lastError = "STARTTLS was rejected by the server: " . trim($res);
                fclose($socket);
                return false;
            }
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $write("EHLO " . $clientHost);
            $read();
        }

        $write("AUTH LOGIN");
        $read();
        $write(base64_encode($user));
        $read();

        $cleanPass = str_replace(' ', '', $pass);
        $write(base64_encode($cleanPass));
        $res = $read();

        if (strpos($res, '235') === false && $cleanPass !== $pass) {
            $write("AUTH LOGIN");
            $read();
            $write(base64_encode($user));
            $read();
            $write(base64_encode($pass));
            $res = $read();
        }

        if (strpos($res, '235') === false) {
            self::$lastError = "SMTP authentication failed for {$user}: " . trim($res);
            error_log("EHR EmailService: " . self::$lastError);
            fclose($socket);
            return false;
        }

        $write("MAIL FROM: <{$fromEmail}>");
        $mailFromRes = $read();
        if (strpos($mailFromRes, '250') === false) {
            self::$lastError = "MAIL FROM <{$fromEmail}> was rejected: " . trim($mailFromRes);
            error_log("EHR EmailService: " . self::$lastError);
            fclose($socket);
            return false;
        }

        $write("RCPT TO: <{$toEmail}>");
        $rcptRes = $read();
        if (strpos($rcptRes, '250') === false && strpos($rcptRes, '251') === false) {
            self::$lastError = "Recipient {$toEmail} was rejected: " . trim($rcptRes);
            error_log("EHR EmailService: " . self::$lastError);
            fclose($socket);
            return false;
        }

        $write("DATA");
        $read();

        // Message-ID must use the SENDER's domain (e.g. gmail.com), not the local dev server's
        // hostname - a mismatched Message-ID domain vs. the From/envelope address is a real spam
        // signal most providers check, and 'localhost'/a made-up local domain always mismatched.
        $senderDomain = (strpos($fromEmail, '@') !== false) ? substr($fromEmail, strpos($fromEmail, '@') + 1) : $host;
        $msgId = "<" . time() . "." . uniqid() . "@" . $senderDomain . ">";
        $dateStr = date('r');

        // Plain Text Fallback Generation from HTML for anti-spam multipart compliance
        $plainText = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $bodyHtml));
        $plainText = preg_replace("/\n\s+/", "\n", $plainText);
        $plainText = trim($plainText);

        $boundary = "----=_NextPart_" . md5(time() . uniqid());

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "To: <{$toEmail}>\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "Date: {$dateStr}\r\n";
        $headers .= "Message-ID: {$msgId}\r\n";
        // RFC 3834 - tells spam filters this is a legitimate automated/transactional message
        // rather than a human-composed one, which some providers weigh in scoring.
        $headers .= "Auto-Submitted: auto-generated\r\n";
        $headers .= "X-Mailer: Specialty EHR System\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n";

        $mimeBody  = "--{$boundary}\r\n";
        $mimeBody .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $mimeBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $mimeBody .= $plainText . "\r\n\r\n";

        $mimeBody .= "--{$boundary}\r\n";
        $mimeBody .= "Content-Type: text/html; charset=UTF-8\r\n";
        $mimeBody .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $mimeBody .= $bodyHtml . "\r\n\r\n";
        $mimeBody .= "--{$boundary}--";

        $write($headers . $mimeBody . "\r\n.");
        $res = $read();

        $write("QUIT");
        fclose($socket);

        $success = (strpos($res, '250') !== false);
        if (!$success) {
            self::$lastError = "Message was rejected during DATA: " . trim($res);
            error_log("EHR EmailService: " . self::$lastError);
        }

        return $success;
    }
}
