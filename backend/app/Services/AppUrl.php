<?php
namespace App\Services;

use App\Models\Database;

/**
 * Builds the public, patient/staff-facing links that go out in emails (intake form, telehealth join,
 * new-user login). Every such link must come from here - never from HTTP_HOST directly - so the
 * Settings > "Public EHR Base URL" value is honoured the same way everywhere.
 *
 * Meaning of the setting: the address of the EHR project folder, i.e. the URL under which `public/` lives
 * (e.g. http://192.168.1.20/Specialty_EHR or https://myclinic.com). It must NOT include /public.
 * Blank = auto-detect from the current request.
 */
class AppUrl {
    /** Full URL to a page under public/, e.g. publicUrl('intake.php?token=abc'). */
    public static function publicUrl(string $page): string {
        $configured = null;
        try {
            $row = Database::fetch("SELECT setting_value FROM system_settings WHERE setting_key = 'app_base_url'");
            if ($row && !empty($row['setting_value'])) {
                $configured = $row['setting_value'];
            }
        } catch (\Throwable $e) {}

        return self::build($page, $configured);
    }

    /** Pure builder (no DB) so it can be tested with a stubbed $_SERVER. */
    public static function build(string $page, ?string $configured): string {
        $page = ltrim($page, '/');

        $configured = trim((string)$configured);
        if ($configured !== '') {
            // Tolerate a legacy value saved without a scheme (e.g. "myclinic.com").
            if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $configured)) {
                $configured = 'http://' . $configured;
            }
            $base = self::normalize($configured);
            if ($base !== null) {
                return $base . '/public/' . $page;
            }
            // Unusable stored value: fall through to auto-detection rather than emitting a broken link.
        }

        $scheme = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')) ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        // A link built from "localhost" only works on the server itself; swap in the LAN IP so phones
        // and other devices on the same network can open it.
        $hostName = strtolower(preg_replace('/:\d+$/', '', $host));
        if ($hostName === 'localhost' || $hostName === '127.0.0.1') {
            $lanIp = gethostbyname(gethostname());
            if ($lanIp && $lanIp !== '127.0.0.1' && filter_var($lanIp, FILTER_VALIDATE_IP)) {
                $port = $_SERVER['SERVER_PORT'] ?? 80;
                $host = $lanIp . (($port != 80 && $port != 443) ? ':' . $port : '');
            }
        }

        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $baseDir = $scriptPath === '' ? '' : dirname(dirname($scriptPath));
        if ($baseDir === '/' || $baseDir === '.' || $baseDir === '\\') {
            $baseDir = '';
        }

        return $scheme . '://' . $host . $baseDir . '/public/' . $page;
    }

    /**
     * Validates and cleans a user-entered base URL. Returns the normalized value (no trailing slash,
     * no /public suffix) or null when it is not a usable http(s) URL.
     */
    public static function normalize(string $raw): ?string {
        $v = trim($raw);
        if ($v === '' || preg_match('/\s/', $v)) {
            return null;
        }

        $p = parse_url($v);
        if ($p === false
            || empty($p['scheme']) || !in_array(strtolower($p['scheme']), ['http', 'https'], true)
            || empty($p['host'])
            || isset($p['query']) || isset($p['fragment']) || isset($p['user']) || isset($p['pass'])) {
            return null;
        }

        $path = rtrim($p['path'] ?? '', '/');
        $path = preg_replace('#/public(/index\.php)?$#i', '', $path);

        return strtolower($p['scheme']) . '://' . $p['host'] . (isset($p['port']) ? ':' . $p['port'] : '') . $path;
    }
}
