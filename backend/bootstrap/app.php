<?php
// Core Bootstrap File

// Autoloader mapping namespaces to directories
spl_autoload_register(function ($class) {
    // Project root directory
    $baseDir = dirname(__DIR__) . '/';

    // Map namespace namespaces to directories
    $prefixMap = [
        'App\\' => $baseDir . 'app/',
        'Routes\\' => $baseDir . 'routes/'
    ];

    foreach ($prefixMap as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $dir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

// Load configuration
$securityConfig = require __DIR__ . '/../config/security.php';

// Set secure headers
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://meet.jit.si https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; frame-src 'self' https://meet.jit.si; connect-src 'self' https://meet.jit.si wss://meet.jit.si; frame-ancestors 'none';");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(self \"https://meet.jit.si\"), microphone=(self \"https://meet.jit.si\"), geolocation=()");
header("Strict-Transport-Security: max-age=63072000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");

// Initialize Secure Session
ini_set('session.cookie_httponly', $securityConfig['session']['cookie_httponly'] ? '1' : '0');
ini_set('session.cookie_secure', $securityConfig['session']['cookie_secure'] ? '1' : '0');
ini_set('session.cookie_samesite', $securityConfig['session']['cookie_samesite']);
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', $securityConfig['session']['lifetime']);

session_start();

// Auto-migrate schema additions if missing
try {
    $db = \App\Models\Database::getConnection();
    $cols = $db->query("SHOW COLUMNS FROM clinical_notes")->fetchAll(\PDO::FETCH_COLUMN);
    if (!in_array('allergies', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN allergies TEXT DEFAULT NULL AFTER vital_bmi");
    }
    if (!in_array('pmh', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN pmh TEXT DEFAULT NULL AFTER allergies");
    }
    if (!in_array('current_medications', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN current_medications TEXT DEFAULT NULL AFTER pmh");
    }
    if (!in_array('icd10_codes', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN icd10_codes TEXT DEFAULT NULL AFTER current_medications");
    }
    if (!in_array('family_history', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN family_history TEXT DEFAULT NULL AFTER icd10_codes");
    }
    if (!in_array('fm_assessment', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN fm_assessment TEXT DEFAULT NULL AFTER family_history");
    }
    if (!in_array('functional_assessment', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN functional_assessment TEXT DEFAULT NULL AFTER fm_assessment");
    }
    if (!in_array('primary_care_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN primary_care_data TEXT DEFAULT NULL AFTER functional_assessment");
    }
    if (!in_array('peds_newborn_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_newborn_data TEXT DEFAULT NULL AFTER primary_care_data");
    }
    if (!in_array('peds_one_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_one_month_data TEXT DEFAULT NULL AFTER peds_newborn_data");
    }
    if (!in_array('peds_two_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_two_month_data TEXT DEFAULT NULL AFTER peds_one_month_data");
    }
    if (!in_array('peds_four_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_four_month_data TEXT DEFAULT NULL AFTER peds_two_month_data");
    }
    if (!in_array('peds_six_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_six_month_data TEXT DEFAULT NULL AFTER peds_four_month_data");
    }
    if (!in_array('peds_nine_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_nine_month_data TEXT DEFAULT NULL AFTER peds_six_month_data");
    }
    if (!in_array('peds_twelve_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_twelve_month_data TEXT DEFAULT NULL AFTER peds_nine_month_data");
    }
    if (!in_array('peds_fifteen_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_fifteen_month_data TEXT DEFAULT NULL AFTER peds_twelve_month_data");
    }
    if (!in_array('peds_eighteen_month_data', $cols)) {
        $db->exec("ALTER TABLE clinical_notes ADD COLUMN peds_eighteen_month_data TEXT DEFAULT NULL AFTER peds_fifteen_month_data");
    }

    // Auto-migrate specialty EHR data columns if missing
    $specialtyCols = [
        'ortho_data'   => 'TEXT DEFAULT NULL',
        'derma_data'   => 'TEXT DEFAULT NULL',
        'neuro_data'   => 'TEXT DEFAULT NULL',
        'onco_data'    => 'TEXT DEFAULT NULL',
        'ophthal_data' => 'TEXT DEFAULT NULL',
        'pt_data'      => 'TEXT DEFAULT NULL',
        'cardio_data'  => 'TEXT DEFAULT NULL',
    ];
    foreach ($specialtyCols as $scName => $scDef) {
        if (!in_array($scName, $cols)) {
            $db->exec("ALTER TABLE clinical_notes ADD COLUMN {$scName} {$scDef}");
        }
    }

    $apptCols = $db->query("SHOW COLUMNS FROM appointments")->fetchAll(\PDO::FETCH_COLUMN);
    if (!in_array('specialty', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN specialty VARCHAR(100) DEFAULT 'Family Medicine (Internal Medicine)' AFTER status");
    }
    if (!in_array('facility', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN facility VARCHAR(100) DEFAULT 'raj'");
    }
    if (!in_array('category', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN category VARCHAR(50) DEFAULT 'Appointment'");
    }
    if (!in_array('visit_type', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN visit_type VARCHAR(100) DEFAULT 'Family Care'");
    }
    if (!in_array('appointment_mode', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN appointment_mode VARCHAR(50) DEFAULT 'In Person'");
    }
    if (!in_array('appointment_for', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN appointment_for VARCHAR(50) DEFAULT 'Single Date'");
    }
    if (!in_array('period_frequency', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN period_frequency VARCHAR(50) DEFAULT NULL");
    }
    if (!in_array('period_count', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN period_count INT DEFAULT 1");
    }
    if (!in_array('message_to_patient', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN message_to_patient TEXT DEFAULT NULL");
    }
    if (!in_array('waiting_list_data', $apptCols)) {
        $db->exec("ALTER TABLE appointments ADD COLUMN waiting_list_data TEXT DEFAULT NULL");
    }
    $db->exec("ALTER TABLE appointments MODIFY COLUMN status VARCHAR(50) DEFAULT 'Scheduled'");

    // Auto-migrate patients table columns if missing
    $patCols = $db->query("SHOW COLUMNS FROM patients")->fetchAll(\PDO::FETCH_COLUMN);
    $newPatCols = [
        'middle_name' => "VARCHAR(100) DEFAULT NULL",
        'gender' => "VARCHAR(20) DEFAULT NULL",
        'marital_status' => "VARCHAR(20) DEFAULT NULL",
        'language' => "VARCHAR(50) DEFAULT NULL",
        'race' => "VARCHAR(50) DEFAULT NULL",
        'home_phone_encrypted' => "TEXT DEFAULT NULL",
        'work_phone_encrypted' => "TEXT DEFAULT NULL",
        'city' => "VARCHAR(100) DEFAULT NULL",
        'state' => "VARCHAR(50) DEFAULT NULL",
        'zip' => "VARCHAR(20) DEFAULT NULL",
        'emergency_contact_name' => "VARCHAR(150) DEFAULT NULL",
        'emergency_relationship' => "VARCHAR(50) DEFAULT NULL",
        'emergency_phone' => "VARCHAR(50) DEFAULT NULL"
    ];
    foreach ($newPatCols as $cName => $cDef) {
        if (!in_array($cName, $patCols)) {
            $db->exec("ALTER TABLE patients ADD COLUMN {$cName} {$cDef}");
        }
    }

    // Auto-migrate patient_insurance table columns if missing
    $insCols = $db->query("SHOW COLUMNS FROM patient_insurance")->fetchAll(\PDO::FETCH_COLUMN);
    $newInsCols = [
        'payer_id' => "VARCHAR(50) DEFAULT NULL",
        'plan_name' => "VARCHAR(100) DEFAULT NULL",
        'effective_date' => "DATE DEFAULT NULL",
        'subscriber_name' => "VARCHAR(150) DEFAULT NULL",
        'subscriber_dob' => "DATE DEFAULT NULL",
        'subscriber_relationship' => "VARCHAR(50) DEFAULT NULL"
    ];
    foreach ($newInsCols as $cName => $cDef) {
        if (!in_array($cName, $insCols)) {
            $db->exec("ALTER TABLE patient_insurance ADD COLUMN {$cName} {$cDef}");
        }
    }

    // Create system_settings table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Seed default system settings
    $defaultSettings = [
        'theme' => 'light',
        'open_time' => '08:00',
        'close_time' => '18:00',
        'closed_days' => json_encode(['0', '6']),
        'holidays' => json_encode([]),
        'preferences' => json_encode(['ambient_scribe' => true, 'auto_save' => true, 'email_reminders' => false])
    ];
    foreach ($defaultSettings as $k => $v) {
        $exists = $db->query("SELECT 1 FROM system_settings WHERE setting_key = " . $db->quote($k))->fetch();
        if (!$exists) {
            $db->exec("INSERT INTO system_settings (setting_key, setting_value) VALUES (" . $db->quote($k) . ", " . $db->quote($v) . ")");
        }
    }

    // Create rbac_policies table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS rbac_policies (
        role VARCHAR(50) PRIMARY KEY,
        encounter_access VARCHAR(50) DEFAULT 'Full Access',
        demographics_access VARCHAR(50) DEFAULT 'Full Access',
        billing_access VARCHAR(50) DEFAULT 'Full Access',
        audit_access VARCHAR(50) DEFAULT 'Full Access',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Seed default RBAC policies
    $defaultRbac = [
        ['Super Admin', 'Full Access', 'Full Access', 'Full Access', 'Full Access'],
        ['Doctor', 'Full Access', 'Full Access', 'Create / View', 'View Logs'],
        ['Therapist', 'Full Access', 'View Only', 'Forbidden', 'Forbidden'],
        ['Billing Staff', 'View Only', 'View Only', 'Full Access', 'Forbidden']
    ];
    foreach ($defaultRbac as $r) {
        $exists = $db->query("SELECT 1 FROM rbac_policies WHERE role = " . $db->quote($r[0]))->fetch();
        if (!$exists) {
            $db->exec("INSERT INTO rbac_policies (role, encounter_access, demographics_access, billing_access, audit_access) VALUES (" . $db->quote($r[0]) . ", " . $db->quote($r[1]) . ", " . $db->quote($r[2]) . ", " . $db->quote($r[3]) . ", " . $db->quote($r[4]) . ")");
        }
    }
} catch (\Exception $e) {
    error_log("Schema migration notice: " . $e->getMessage());
}

// Handle Session fingerprint & Idle timeout (15 mins)
if (isset($_SESSION['user_id'])) {
    $isApi = (strpos($_SERVER['REQUEST_URI'], '/api/') !== false);

    // Check Idle Timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $securityConfig['session']['lifetime'])) {
        session_unset();
        session_destroy();
        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Session expired due to inactivity. Please log in again.'
            ]);
            exit();
        } else {
            // For HTML requests, redirect to clear the hash or reload SPA
            header("Location: " . $_SERVER['SCRIPT_NAME']);
            exit();
        }
    }
    $_SESSION['last_activity'] = time();

    // Check Fingerprint
    $fingerprint = md5($_SERVER['REMOTE_ADDR'] . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if (!isset($_SESSION['fingerprint']) || $_SESSION['fingerprint'] !== $fingerprint) {
        session_unset();
        session_destroy();
        if ($isApi) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Session hijacking detected. Access denied.'
            ]);
            exit();
        } else {
            header("Location: " . $_SERVER['SCRIPT_NAME']);
            exit();
        }
    }

    // Dynamic session regeneration to prevent session fixation (every 5 minutes / 300s)
    if (!isset($_SESSION['created_time'])) {
        $_SESSION['created_time'] = time();
    } elseif (time() - $_SESSION['created_time'] > 300) {
        session_regenerate_id(true);
        $_SESSION['created_time'] = time();
    }
}
