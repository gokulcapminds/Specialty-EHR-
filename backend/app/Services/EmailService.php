<?php
namespace App\Services;

class EmailService {

    /**
     * Send email via SMTP socket or PHP mail fallback
     */
    public static function send(string $toEmail, string $subject, string $bodyHtml, string $fromName = 'CareHealth Family Medicine'): bool {
        $toEmail = trim($toEmail);
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            error_log("EHR EmailService: Invalid recipient email address '{$toEmail}'.");
            return false;
        }

        // Fetch dynamic SMTP configuration from DB system_settings table
        $dbSettings = [];
        try {
            $rows = \App\Models\Database::fetchAll("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'smtp_%'");
            foreach ($rows as $r) {
                $dbSettings[$r['setting_key']] = $r['setting_value'];
            }
        } catch (\Throwable $t) {}

        $smtpHost = !empty($dbSettings['smtp_host']) ? trim($dbSettings['smtp_host']) : (getenv('SMTP_HOST') ?: 'smtp.gmail.com');
        $smtpPort = !empty($dbSettings['smtp_port']) ? intval($dbSettings['smtp_port']) : intval(getenv('SMTP_PORT') ?: 465);
        $smtpUser = !empty($dbSettings['smtp_user']) ? trim($dbSettings['smtp_user']) : (getenv('SMTP_USER') ?: 'sssivaprasad6@gmail.com');
        $smtpPass = !empty($dbSettings['smtp_pass']) ? trim($dbSettings['smtp_pass']) : (getenv('SMTP_PASS') ?: 'momhnyznniaayzvm');
        $smtpSecure = !empty($dbSettings['smtp_secure']) ? trim($dbSettings['smtp_secure']) : (getenv('SMTP_SECURE') ?: 'ssl');
        $fromEmail = !empty($dbSettings['smtp_from']) ? trim($dbSettings['smtp_from']) : (getenv('SMTP_FROM') ?: ($smtpUser ?: 'sssivaprasad6@gmail.com'));

        // If SMTP credentials are provided, attempt socket SMTP connection
        if (!empty($smtpUser) && !empty($smtpPass)) {
            try {
                $sent = self::sendSmtp($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure, $fromEmail, $fromName, $toEmail, $subject, $bodyHtml);
                if ($sent) return true;
                error_log("EHR EmailService: SMTP transport failed for user {$smtpUser} when sending to {$toEmail}.");
            } catch (\Throwable $e) {
                error_log("EHR EmailService SMTP Exception: " . $e->getMessage());
            }
        }

        // Standard Mail Fallback
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return @mail($toEmail, $subject, $bodyHtml, $headers);
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
            error_log("EHR EmailService: fsockopen failed to {$transport}:{$port} - Error: {$errno} {$errstr}");
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
            error_log("EHR EmailService: SMTP AUTH LOGIN failed: " . trim($res));
            fclose($socket);
            return false;
        }

        $write("MAIL FROM: <{$fromEmail}>");
        $mailFromRes = $read();

        $write("RCPT TO: <{$toEmail}>");
        $rcptRes = $read();
        if (strpos($rcptRes, '250') === false && strpos($rcptRes, '251') === false) {
            error_log("EHR EmailService: RCPT TO rejected for {$toEmail}: " . trim($rcptRes));
            fclose($socket);
            return false;
        }

        $write("DATA");
        $read();

        $domainPart = (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.') !== false) ? $_SERVER['HTTP_HOST'] : 'carehealth-ehr.local';
        $msgId = "<" . time() . "." . uniqid() . "@" . preg_replace('/:[0-9]+$/', '', $domainPart) . ">";
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
        $headers .= "X-Mailer: CareHealth EHR System\r\n";
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
            error_log("EHR EmailService: SMTP DATA rejected: " . trim($res));
        }

        return $success;
    }
}
