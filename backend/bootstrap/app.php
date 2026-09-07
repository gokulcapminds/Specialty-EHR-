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

    // Auto-migrate users columns if missing
    $userCols = $db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN);
    if (!in_array('specialty', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN specialty VARCHAR(100) DEFAULT 'Primary Care' AFTER role");
    }

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

    // Auto-migrate all clinical_notes columns if missing
    $cnNewCols = [
        'vital_pulse_pattern'       => 'VARCHAR(50) DEFAULT NULL',
        'vital_pulse_volume'        => 'VARCHAR(50) DEFAULT NULL',
        'growth_weight_percentile'  => 'VARCHAR(20) DEFAULT NULL',
        'growth_height_percentile'  => 'VARCHAR(20) DEFAULT NULL',
        'immunizations_administered'=> 'TEXT DEFAULT NULL',
        'obgyn_lmp'                 => 'DATE DEFAULT NULL',
        'obgyn_edd'                 => 'DATE DEFAULT NULL',
        'obgyn_gravida'             => 'INT DEFAULT NULL',
        'obgyn_para'                => 'INT DEFAULT NULL',
        'obgyn_abortions'           => 'INT DEFAULT NULL',
        'obgyn_living'              => 'INT DEFAULT NULL',
        'obgyn_fundal_height'       => 'VARCHAR(50) DEFAULT NULL',
        'obgyn_fetal_heart_rate'    => 'VARCHAR(50) DEFAULT NULL',
        'pediatric_data'            => 'TEXT DEFAULT NULL',
        'obgyn_data'                => 'TEXT DEFAULT NULL',
        'ortho_data'                => 'TEXT DEFAULT NULL',
        'derma_data'                => 'TEXT DEFAULT NULL',
        'neuro_data'                => 'TEXT DEFAULT NULL',
        'onco_data'                 => 'TEXT DEFAULT NULL',
        'ophthal_data'              => 'TEXT DEFAULT NULL',
        'pt_data'                   => 'TEXT DEFAULT NULL',
        'cardio_data'               => 'TEXT DEFAULT NULL'
    ];
    foreach ($cnNewCols as $cnName => $cnDef) {
        if (!in_array($cnName, $cols)) {
            $db->exec("ALTER TABLE clinical_notes ADD COLUMN {$cnName} {$cnDef}");
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
        'age' => "INT DEFAULT NULL",
        'marital_status' => "VARCHAR(20) DEFAULT NULL",
        'language' => "VARCHAR(50) DEFAULT NULL",
        'race' => "VARCHAR(50) DEFAULT NULL",
        'ethnicity' => "VARCHAR(50) DEFAULT NULL",
        'smoking_status' => "VARCHAR(50) DEFAULT NULL",
        'employment_status' => "VARCHAR(50) DEFAULT NULL",
        'sexual_orientation' => "VARCHAR(50) DEFAULT NULL",
        'sexual_orientation_declined' => "TINYINT(1) DEFAULT 0",
        'home_phone_encrypted' => "TEXT DEFAULT NULL",
        'work_phone_encrypted' => "TEXT DEFAULT NULL",
        'work_phone_ext' => "VARCHAR(20) DEFAULT NULL",
        'address_line2' => "VARCHAR(255) DEFAULT NULL",
        'city' => "VARCHAR(100) DEFAULT NULL",
        'state' => "VARCHAR(50) DEFAULT NULL",
        'country' => "VARCHAR(100) DEFAULT 'United States'",
        'zip' => "VARCHAR(20) DEFAULT NULL",
        'is_po_box' => "TINYINT(1) DEFAULT 0",
        'county' => "VARCHAR(100) DEFAULT NULL",
        'primary_provider_id' => "INT DEFAULT NULL",
        'gender_identity' => "VARCHAR(50) DEFAULT NULL",
        'pronouns' => "VARCHAR(50) DEFAULT NULL",
        'nickname' => "VARCHAR(100) DEFAULT NULL",
        'suffix' => "VARCHAR(20) DEFAULT NULL",
        'maiden_name' => "VARCHAR(100) DEFAULT NULL",
        'previous_name' => "VARCHAR(100) DEFAULT NULL",
        'linked_patient' => "VARCHAR(100) DEFAULT NULL",
        'patient_ids_json' => "TEXT DEFAULT NULL",
        'previous_address_json' => "TEXT DEFAULT NULL",
        'emergency_contact_name' => "VARCHAR(150) DEFAULT NULL",
        'emergency_relationship' => "VARCHAR(50) DEFAULT NULL",
        'emergency_phone' => "VARCHAR(50) DEFAULT NULL",
        'emergency_phone_ext' => "VARCHAR(20) DEFAULT NULL",
        'caregivers_json' => "TEXT DEFAULT NULL",
        'guarantor_json' => "TEXT DEFAULT NULL",
        'preferred_communication' => "VARCHAR(50) DEFAULT 'Email'",
        'email_notifications' => "TINYINT(1) DEFAULT 1",
        'text_notifications' => "TINYINT(1) DEFAULT 1",
        'voice_notifications' => "TINYINT(1) DEFAULT 1",
        'phr_invitation' => "VARCHAR(50) DEFAULT 'To Patient'",
        'category' => "VARCHAR(50) DEFAULT 'General'",
        'payment_source' => "VARCHAR(50) DEFAULT 'Self-Pay'",
        'blood_group' => "VARCHAR(10) DEFAULT NULL",
        'mother_maiden_first_name' => "VARCHAR(100) DEFAULT NULL",
        'mother_maiden_last_name' => "VARCHAR(100) DEFAULT NULL",
        'multiple_birth' => "TINYINT(1) DEFAULT 0",
        'about_patient' => "TEXT DEFAULT NULL",
        'hear_source' => "VARCHAR(100) DEFAULT NULL",
        'hear_specific_source' => "VARCHAR(100) DEFAULT NULL",
        'photo_url' => "TEXT DEFAULT NULL"
    ];
    foreach ($newPatCols as $cName => $cDef) {
        if (!in_array($cName, $patCols)) {
            $db->exec("ALTER TABLE patients ADD COLUMN {$cName} {$cDef}");
        }
    }

    // Auto-create patient_insurance table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS patient_insurance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        primary_provider VARCHAR(150) DEFAULT NULL,
        primary_policy_no VARCHAR(100) DEFAULT NULL,
        primary_group_no VARCHAR(100) DEFAULT NULL,
        plan_name VARCHAR(100) DEFAULT NULL,
        payer_id VARCHAR(50) DEFAULT NULL,
        effective_date DATE DEFAULT NULL,
        subscriber_name VARCHAR(150) DEFAULT NULL,
        subscriber_dob DATE DEFAULT NULL,
        subscriber_relationship VARCHAR(50) DEFAULT 'Self',
        copay VARCHAR(50) DEFAULT NULL,
        insurance_phone VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pi_patient_id (patient_id)
    )");

    // Auto-create patient_intake_forms table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS patient_intake_forms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        token VARCHAR(64) UNIQUE NOT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        consent_signed TINYINT(1) DEFAULT 0,
        hipaa_signed TINYINT(1) DEFAULT 0,
        signature_data LONGTEXT DEFAULT NULL,
        submitted_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pif_patient_id (patient_id),
        INDEX idx_pif_token (token)
    )");

    // Auto-migrate patient_insurance table columns if missing
    $insCols = $db->query("SHOW COLUMNS FROM patient_insurance")->fetchAll(\PDO::FETCH_COLUMN);
    $newInsCols = [
        'payer_id' => "VARCHAR(50) DEFAULT NULL",
        'plan_name' => "VARCHAR(100) DEFAULT NULL",
        'effective_date' => "DATE DEFAULT NULL",
        'subscriber_name' => "VARCHAR(150) DEFAULT NULL",
        'subscriber_dob' => "DATE DEFAULT NULL",
        'subscriber_relationship' => "VARCHAR(50) DEFAULT NULL",
        'copay' => "VARCHAR(50) DEFAULT NULL",
        'insurance_phone' => "VARCHAR(50) DEFAULT NULL"
    ];
    foreach ($newInsCols as $cName => $cDef) {
        if (!in_array($cName, $insCols)) {
            $db->exec("ALTER TABLE patient_insurance ADD COLUMN {$cName} {$cDef}");
        }
    }

    // Auto-create patient_referrals table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS patient_referrals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        referring_provider_id INT DEFAULT NULL,
        department VARCHAR(100) DEFAULT 'Outpatient',
        specialist_name VARCHAR(150) NOT NULL,
        specialty VARCHAR(100) NOT NULL,
        reason_for_referral TEXT DEFAULT NULL,
        referral_type VARCHAR(50) DEFAULT 'Consultation',
        priority VARCHAR(50) DEFAULT 'Routine',
        referral_date DATE DEFAULT NULL,
        clinical_documentation TEXT DEFAULT NULL,
        document_path VARCHAR(255) DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_pref_patient_id (patient_id),
        INDEX idx_pref_status (status)
    )");

    // Auto-create patient_recalls table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS patient_recalls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        recall_type VARCHAR(100) NOT NULL,
        target_date DATE NOT NULL,
        priority VARCHAR(50) DEFAULT 'Normal',
        internal_note TEXT DEFAULT NULL,
        provider_id INT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_prec_patient_id (patient_id),
        INDEX idx_prec_status (status)
    )");

    // Auto-create patient_recall_attempts table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS patient_recall_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        recall_id INT NOT NULL,
        patient_id INT NOT NULL,
        channel VARCHAR(50) NOT NULL,
        outcome VARCHAR(100) NOT NULL,
        note TEXT DEFAULT NULL,
        logged_by INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pra_recall_id (recall_id),
        INDEX idx_pra_patient_id (patient_id)
    )");

    // Auto-create cpt_charge_master table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS cpt_charge_master (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cpt_code VARCHAR(50) UNIQUE NOT NULL,
        description VARCHAR(255) NOT NULL,
        default_charge DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        category VARCHAR(100) DEFAULT 'Primary Care',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Seed default CPT codes if table empty
    $cptCount = $db->query("SELECT COUNT(*) FROM cpt_charge_master")->fetchColumn();
    if ($cptCount == 0) {
        $db->exec("INSERT INTO cpt_charge_master (cpt_code, description, default_charge, category) VALUES
            ('99202', 'Office/Outpatient Visit New (15-29 min)', 115.00, 'Primary Care'),
            ('99203', 'Office/Outpatient Visit New (30-44 min)', 160.00, 'Primary Care'),
            ('99204', 'Office/Outpatient Visit New (45-59 min)', 225.00, 'Primary Care'),
            ('99212', 'Office/Outpatient Visit Est (10-19 min)', 75.00, 'Primary Care'),
            ('99213', 'Office/Outpatient Visit Est (20-29 min)', 110.00, 'Primary Care'),
            ('99214', 'Office/Outpatient Visit Est (30-39 min)', 165.00, 'Primary Care'),
            ('99215', 'Office/Outpatient Visit Est (40-54 min)', 220.00, 'Primary Care'),
            ('99385', 'Initial Preventative Exam 18-39 yrs', 195.00, 'Primary Care'),
            ('99395', 'Periodic Preventative Exam 18-39 yrs', 170.00, 'Primary Care'),
            ('90471', 'Immunization Administration', 35.00, 'Pediatrics'),
            ('90707', 'MMR Vaccine Admin', 65.00, 'Pediatrics'),
            ('80053', 'Comprehensive Metabolic Panel', 55.00, 'General'),
            ('85025', 'Complete Blood Count (CBC)', 35.00, 'General'),
            ('93000', 'Electrocardiogram (ECG/EKG)', 85.00, 'Cardiology'),
            ('71046', 'Chest X-Ray 2 Views', 95.00, 'Radiology')
        ");
    }

    // Auto-create invoices table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        encounter_id INT DEFAULT NULL,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        invoice_date DATE NOT NULL,
        due_date DATE NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        discount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        paid_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        status VARCHAR(50) DEFAULT 'Issued',
        payment_method VARCHAR(50) DEFAULT NULL,
        payment_notes TEXT DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_inv_patient (patient_id),
        INDEX idx_inv_status (status)
    )");

    // Auto-create invoice_line_items table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS invoice_line_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_id INT NOT NULL,
        icd10_code VARCHAR(50) DEFAULT NULL,
        icd10_description VARCHAR(255) DEFAULT NULL,
        cpt_code VARCHAR(50) DEFAULT NULL,
        cpt_description VARCHAR(255) DEFAULT NULL,
        quantity INT DEFAULT 1,
        unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        total_price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ili_invoice_id (invoice_id)
    )");

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
