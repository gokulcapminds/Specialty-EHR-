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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://meet.jit.si https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data:; frame-src 'self' https://meet.jit.si; connect-src 'self' https://meet.jit.si wss://meet.jit.si https://cdn.jsdelivr.net; frame-ancestors 'none';");
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

// WAMP/Windows occasionally fails to open the session file on the first attempt (antivirus
// real-time scanning or file-locking contention from the SPA's parallel AJAX calls both hitting
// the same session file) - session_start() then silently returns an EMPTY $_SESSION rather than
// throwing, so a still-logged-in admin can get a false "permission required" on a single request.
// One quick retry clears most of these transient failures without masking a real problem.
if (!@session_start()) {
    usleep(50000); // 50ms
    session_start();
}

// Auto-migrate schema additions if missing
try {
    $db = \App\Models\Database::getConnection();

    // Auto-migrate users columns if missing
    $userCols = $db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN);
    if (!in_array('specialty', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN specialty VARCHAR(100) DEFAULT 'Primary Care' AFTER role");
    }
    if (!in_array('npi', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN npi VARCHAR(20) DEFAULT NULL AFTER specialty");
    }
    if (!in_array('license_number', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN license_number VARCHAR(50) DEFAULT NULL AFTER npi");
    }
    if (!in_array('license_expiry', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN license_expiry DATE DEFAULT NULL AFTER license_number");
    }
    if (!in_array('dea_number', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN dea_number VARCHAR(50) DEFAULT NULL AFTER license_expiry");
    }
    if (!in_array('dea_expiry', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN dea_expiry DATE DEFAULT NULL AFTER dea_number");
    }
    if (!in_array('credential_type', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN credential_type VARCHAR(50) DEFAULT NULL AFTER dea_expiry");
    }
    if (!in_array('sub_specialty', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN sub_specialty VARCHAR(100) DEFAULT NULL AFTER credential_type");
    }
    if (!in_array('taxonomy_code', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN taxonomy_code VARCHAR(50) DEFAULT NULL AFTER sub_specialty");
    }
    if (!in_array('phone', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) DEFAULT NULL AFTER taxonomy_code");
    }
    if (!in_array('address', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN address TEXT DEFAULT NULL AFTER phone");
    }
    if (!in_array('provider_locations', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN provider_locations TEXT DEFAULT NULL AFTER address");
    }
    if (!in_array('provider_schedule', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN provider_schedule TEXT DEFAULT NULL AFTER provider_locations");
    }
    if (!in_array('provider_billing', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN provider_billing TEXT DEFAULT NULL AFTER provider_schedule");
    }
    if (!in_array('provider_preferences', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN provider_preferences TEXT DEFAULT NULL AFTER provider_billing");
    }
    if (!in_array('invite_token', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN invite_token VARCHAR(64) DEFAULT NULL AFTER provider_preferences");
    }
    if (!in_array('invite_token_expires', $userCols)) {
        $db->exec("ALTER TABLE users ADD COLUMN invite_token_expires DATETIME DEFAULT NULL AFTER invite_token");
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

    // Auto-migrate patient_intake_forms table columns if missing
    $intakeCols = $db->query("SHOW COLUMNS FROM patient_intake_forms")->fetchAll(\PDO::FETCH_COLUMN);
    $newIntakeCols = [
        'consent_agreed' => "TINYINT(1) DEFAULT 0",
        'consent_name' => "VARCHAR(150) DEFAULT NULL",
        'consent_signature' => "LONGTEXT DEFAULT NULL",
        'consent_signed_date' => "DATE DEFAULT NULL",
        'hipaa_agreed' => "TINYINT(1) DEFAULT 0",
        'hipaa_name' => "VARCHAR(150) DEFAULT NULL",
        'hipaa_signature' => "LONGTEXT DEFAULT NULL",
        'hipaa_signed_date' => "DATE DEFAULT NULL"
    ];
    foreach ($newIntakeCols as $cName => $cDef) {
        if (!in_array($cName, $intakeCols)) {
            $db->exec("ALTER TABLE patient_intake_forms ADD COLUMN {$cName} {$cDef}");
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

    // Seed default RBAC policies for all 6 staff-assignable roles (users.role ENUM minus 'Patient')
    $defaultRbac = [
        ['Super Admin', 'Full Access', 'Full Access', 'Full Access', 'Full Access'],
        ['Doctor', 'Full Access', 'Full Access', 'Create / View', 'View Only'],
        ['Therapist', 'Full Access', 'View Only', 'Forbidden', 'Forbidden'],
        ['Nurse', 'Full Access', 'View Only', 'Forbidden', 'Forbidden'],
        ['Receptionist', 'View Only', 'Full Access', 'Forbidden', 'Forbidden'],
        ['Billing Staff', 'View Only', 'View Only', 'Full Access', 'Forbidden']
    ];
    // Auto-migrate users table columns for custom permissions & provider details if missing
    $userCols = $db->query("SHOW COLUMNS FROM users")->fetchAll(\PDO::FETCH_COLUMN);
    $newUserCols = [
        'custom_permissions' => 'JSON DEFAULT NULL',
        'provider_locations' => 'JSON DEFAULT NULL',
        'provider_schedule' => 'JSON DEFAULT NULL',
        'provider_billing' => 'JSON DEFAULT NULL',
        'provider_preferences' => 'JSON DEFAULT NULL',
        'invite_token' => 'VARCHAR(64) DEFAULT NULL',
        'invite_token_expires' => 'DATETIME DEFAULT NULL'
    ];
    foreach ($newUserCols as $cName => $cDef) {
        if (!in_array($cName, $userCols)) {
            $db->exec("ALTER TABLE users ADD COLUMN {$cName} {$cDef}");
        }
    }

    // Auto-create custom_roles table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS custom_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description VARCHAR(255) DEFAULT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-id-badge',
        base_role VARCHAR(50) NOT NULL DEFAULT 'Receptionist',
        permissions JSON NOT NULL,
        is_system TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Seed default role templates if empty
    $seedTemplates = [
        [
            'name' => 'Medical Assistant',
            'description' => 'Patient intake, documentation, orders, vitals.',
            'icon' => 'fas fa-stethoscope',
            'base_role' => 'Nurse',
            'is_system' => 1,
            'permissions' => json_encode([
                'View patient demographics', 'Register new patients', 'Update patient information',
                'View clinical notes', 'Add documentation', 'Record vitals', 'Place orders', 'View lab results',
                'View schedule', 'Create appointments', 'Reschedule / Cancel',
                'Access all locations in practice', 'Access telehealth virtual clinics', 'Access all practice patient charts', 'Mask Social Security Numbers',
                'Broadcast practice announcements', 'Send direct secure staff messages', 'Send patient SMS & Email reminders',
                'Host video consultations', 'Share screen & digital whiteboard'
            ])
        ],
        [
            'name' => 'Front Desk',
            'description' => 'Scheduling, registration, check-in/out.',
            'icon' => 'fas fa-user-clock',
            'base_role' => 'Receptionist',
            'is_system' => 1,
            'permissions' => json_encode([
                'View patient demographics', 'Register new patients', 'Update patient information',
                'View schedule', 'Create appointments', 'Reschedule / Cancel', 'Block time', 'Manage provider schedules',
                'View billing information', 'View patient balances',
                'Access all locations in practice', 'Access all practice patient charts', 'Mask Social Security Numbers',
                'Broadcast practice announcements', 'Send direct secure staff messages', 'Send patient SMS & Email reminders'
            ])
        ],
        [
            'name' => 'Billing Staff',
            'description' => 'Claims, payments, billing reports.',
            'icon' => 'fas fa-file-invoice-dollar',
            'base_role' => 'Billing Staff',
            'is_system' => 1,
            'permissions' => json_encode([
                'View patient demographics',
                'View billing information', 'Create claims', 'Post payments', 'View patient balances',
                'View standard reports', 'Export reports',
                'Access all locations in practice', 'Access all practice patient charts', 'Mask Social Security Numbers', 'Export PHI to Excel / CSV',
                'Send direct secure staff messages'
            ])
        ],
        [
            'name' => 'Practice Manager',
            'description' => 'Operational access, reports, user oversight.',
            'icon' => 'fas fa-briefcase',
            'base_role' => 'Super Admin',
            'is_system' => 1,
            'permissions' => json_encode([
                'View patient demographics', 'Register new patients', 'Update patient information', 'Merge duplicate patients',
                'View clinical notes', 'Add documentation', 'Record vitals', 'Place orders', 'Manage medications', 'View lab results',
                'View schedule', 'Create appointments', 'Reschedule / Cancel', 'Block time', 'Manage provider schedules',
                'View billing information', 'Create claims', 'Post payments', 'View patient balances',
                'View standard reports', 'Export reports',
                'Manage users', 'Manage practice settings', 'View audit logs',
                'Access all locations in practice', 'Access telehealth virtual clinics', 'Access all practice patient charts', 'Export PHI to Excel / CSV',
                'Create & invite new staff users', 'Edit user roles & security levels', 'Reset staff passwords & MFA',
                'Configure clinical templates & forms', 'Manage fee schedules & CPT codes', 'Manage lab & pharmacy integrations',
                'View system audit logs', 'Export HIPAA audit reports', 'Manage security policies & RBAC matrix',
                'Broadcast practice announcements', 'Send direct secure staff messages', 'Send patient SMS & Email reminders',
                'Host video consultations', 'Share screen & digital whiteboard'
            ])
        ]
    ];

    foreach ($seedTemplates as $tmpl) {
        $exists = $db->query("SELECT 1 FROM custom_roles WHERE name = " . $db->quote($tmpl['name']))->fetch();
        if (!$exists) {
            $stmt = $db->prepare("INSERT INTO custom_roles (name, description, icon, base_role, permissions, is_system) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tmpl['name'], $tmpl['description'], $tmpl['icon'], $tmpl['base_role'], $tmpl['permissions'], $tmpl['is_system']]);
        }
    }

    // Auto-create facilities table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS facilities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        facility_name VARCHAR(150) NOT NULL,
        facility_code VARCHAR(50) DEFAULT NULL,
        legal_entity_name VARCHAR(150) DEFAULT NULL,
        tax_id_ein VARCHAR(50) DEFAULT NULL,
        npi VARCHAR(20) DEFAULT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL,
        website VARCHAR(150) DEFAULT NULL,
        address VARCHAR(255) NOT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        zip_code VARCHAR(20) NOT NULL,
        country VARCHAR(100) DEFAULT 'India',
        description TEXT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $facCount = $db->query("SELECT COUNT(*) FROM facilities")->fetchColumn();
    if ($facCount == 0) {
        $db->exec("INSERT INTO facilities (facility_name, facility_code, legal_entity_name, tax_id_ein, phone, email, address, city, state, zip_code, country, is_active) VALUES
            ('Apollo Healthcare Network', 'FAC-APH-01', 'Apollo Hospitals Enterprise Ltd', 'TAX-987654321', '+91 44 2829 0200', 'admin@apollohealth.com', '21 Greams Lane, Thousand Lights', 'Chennai', 'Tamil Nadu', '600006', 'India', 1),
            ('Westside Medical Group', 'FAC-WMG-02', 'Westside Healthcare Partners LLC', 'TAX-123456789', '+91 422 245 0000', 'contact@westsidemed.com', '100 Medical Center Drive', 'Coimbatore', 'Tamil Nadu', '641018', 'India', 1)
        ");
    }

    // Auto-create locations table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS locations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        facility_id INT DEFAULT 1,
        location_name VARCHAR(150) NOT NULL,
        location_code VARCHAR(50) DEFAULT NULL,
        location_type VARCHAR(100) DEFAULT 'Rural Health Clinic',
        sub_type VARCHAR(100) DEFAULT 'Primary Care Facility',
        photo_url LONGTEXT DEFAULT NULL,
        address VARCHAR(255) NOT NULL,
        suite_building VARCHAR(100) DEFAULT NULL,
        city VARCHAR(100) NOT NULL,
        state VARCHAR(100) NOT NULL,
        zip_code VARCHAR(20) NOT NULL,
        country VARCHAR(100) DEFAULT 'India',
        billing_address VARCHAR(255) DEFAULT NULL,
        phone VARCHAR(50) NOT NULL,
        fax VARCHAR(50) DEFAULT NULL,
        email VARCHAR(100) NOT NULL,
        website VARCHAR(150) DEFAULT NULL,
        manager_name VARCHAR(100) DEFAULT NULL,
        pos_code VARCHAR(100) DEFAULT '11 - Office',
        exam_rooms INT DEFAULT 4,
        npi VARCHAR(20) DEFAULT NULL,
        clia_number VARCHAR(50) DEFAULT NULL,
        state_license VARCHAR(50) DEFAULT NULL,
        special_services TEXT DEFAULT NULL,
        timezone VARCHAR(50) DEFAULT 'Asia/Kolkata',
        hours_of_operation VARCHAR(100) DEFAULT 'Mon - Fri: 8:00 AM - 6:00 PM',
        online_scheduling_enabled TINYINT(1) DEFAULT 1,
        telehealth_enabled TINYINT(1) DEFAULT 1,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_loc_facility (facility_id)
    ) ENGINE=InnoDB");

    // Ensure facility_id column exists if table was created previously without it
    try {
        $cols = $db->query("SHOW COLUMNS FROM locations LIKE 'facility_id'")->fetch();
        if (!$cols) {
            $db->exec("ALTER TABLE locations ADD COLUMN facility_id INT DEFAULT 1 AFTER id");
            $db->exec("ALTER TABLE locations ADD INDEX idx_loc_facility (facility_id)");
        }
    } catch (\Exception $colEx) {}

    $locCount = $db->query("SELECT COUNT(*) FROM locations")->fetchColumn();
    if ($locCount == 0) {
        $db->exec("INSERT INTO locations (facility_id, location_name, location_code, location_type, sub_type, address, city, state, zip_code, country, phone, email, manager_name, pos_code, exam_rooms, special_services, is_active) VALUES
            (1, 'Chennai Rural Clinic', 'LOC-CHE-01', 'Rural Health Clinic', 'Primary Care Facility', '124 Anna Salai', 'Chennai', 'Tamil Nadu', '600002', 'India', '+91 44 2852 1100', 'chennai.clinic@specialtyehr.com', 'Dr. John Smith', '11 - Office', 6, '[\"Primary Care\",\"Telehealth Services\",\"Laboratory Tests\"]', 1),
            (1, 'Main Specialty Center', 'LOC-MSP-02', 'Primary Clinic', 'Comprehensive Outpatient', '45 Healthcare Blvd, Suite 200', 'Coimbatore', 'Tamil Nadu', '641018', 'India', '+91 422 230 4400', 'coimbatore.center@specialtyehr.com', 'Dr. Emily Davis', '22 - On Campus Outpatient Hospital', 10, '[\"Primary Care\",\"Behavioral Health\",\"Pediatrics Care\"]', 1),
            (1, 'Telehealth Remote Hub', 'LOC-TEL-03', 'Telehealth Remote Center', 'Telehealth Hub', 'Online Virtual Care Platform', 'Chennai', 'Tamil Nadu', '600001', 'India', '+91 44 2852 1199', 'telehealth@specialtyehr.com', 'System Admin', '02 - Telehealth Provided Other than in Patient\'s Home', 0, '[\"Telehealth Services\"]', 1)
        ");
    }

    // Auto-create specialties table if missing
    $db->exec("CREATE TABLE IF NOT EXISTS specialties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        specialty_key VARCHAR(100) NOT NULL UNIQUE,
        specialty_name VARCHAR(150) NOT NULL,
        code VARCHAR(50) DEFAULT NULL,
        icon VARCHAR(50) DEFAULT 'fas fa-stethoscope',
        description TEXT DEFAULT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    $specCount = $db->query("SELECT COUNT(*) FROM specialties")->fetchColumn();
    if ($specCount == 0) {
        $db->exec("INSERT INTO specialties (specialty_key, specialty_name, code, icon, description, is_active) VALUES
            ('Cardiology', 'Cardiology (Heart & Vascular)', 'SPEC-CARD', 'fas fa-heartbeat', 'Comprehensive cardiac care, ECG, Echo, and heart disease management.', 1),
            ('Orthopedics', 'Orthopedics & Joint Care', 'SPEC-ORTH', 'fas fa-bone', 'Bone, joint, musculoskeletal trauma, and surgical rehabilitation.', 1),
            ('Dermatology', 'Dermatology & Skin Care', 'SPEC-DERM', 'fas fa-allergies', 'Skin exams, dermatological lesions, biopsies, and cosmetic treatments.', 1),
            ('Neurology', 'Neurology & Brain Sciences', 'SPEC-NEUR', 'fas fa-brain', 'Brain disorders, cranial nerve examinations, stroke, and EEG analysis.', 1),
            ('Oncology', 'Oncology & Cancer Care', 'SPEC-ONCO', 'fas fa-ribbon', 'Tumor staging, chemotherapy regimens, radiation therapy, and oncology charting.', 1),
            ('Ophthalmology', 'Ophthalmology & Vision Care', 'SPEC-OPHT', 'fas fa-eye', 'Vision assessment, IOP tonometry, slit lamp examination, and refraction.', 1),
            ('Physical Therapy', 'Physical Therapy & Rehab', 'SPEC-PHYS', 'fas fa-running', 'Musculoskeletal range of motion, muscle strength, gait analysis, and physical rehab.', 1)
        ");
    }

    // ── Facility <-> Specialty scoping: which specialties each facility actually practices ──
    // Scoped try/catch so a seeding hiccup can't abort every migration statement below it.
    try {
        $db->exec("CREATE TABLE IF NOT EXISTS facility_specialties (
            facility_id INT NOT NULL,
            specialty_id INT NOT NULL,
            PRIMARY KEY (facility_id, specialty_id),
            FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE CASCADE,
            FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE RESTRICT
        ) ENGINE=InnoDB");

        // One-shot backfill only — never re-fires once populated, so an admin deliberately
        // narrowing a facility's specialties later won't get silently overwritten back to all 7.
        $fsCount = $db->query("SELECT COUNT(*) FROM facility_specialties")->fetchColumn();
        if ($fsCount == 0) {
            $facIds = $db->query("SELECT id FROM facilities")->fetchAll(\PDO::FETCH_COLUMN);
            $specIds = $db->query("SELECT id FROM specialties")->fetchAll(\PDO::FETCH_COLUMN);
            $fsStmt = $db->prepare("INSERT IGNORE INTO facility_specialties (facility_id, specialty_id) VALUES (?, ?)");
            foreach ($facIds as $fid) {
                foreach ($specIds as $sid) {
                    $fsStmt->execute([$fid, $sid]);
                }
            }
        }
    } catch (\Exception $fsEx) {
        error_log('facility_specialties migration/seed notice: ' . $fsEx->getMessage());
    }

    // Ensure users table has facility_id, location_id, additional_locations, additional_specialties, weekly_schedule
    $userCols = array_column($db->query("SHOW COLUMNS FROM users")->fetchAll(), 'Field');
    $userColsToAdd = [
        'facility_id'            => 'INT DEFAULT NULL',
        'location_id'            => 'INT DEFAULT NULL',
        'additional_locations'   => 'TEXT DEFAULT NULL',
        'additional_specialties' => 'TEXT DEFAULT NULL',
        'weekly_schedule'        => 'JSON DEFAULT NULL',
        'gender'                 => 'VARCHAR(30) DEFAULT NULL',
        'dob'                    => 'DATE DEFAULT NULL',
        'marital_status'         => 'VARCHAR(30) DEFAULT NULL',
        'nationality'            => 'VARCHAR(50) DEFAULT NULL',
        'mobile_phone'           => 'VARCHAR(50) DEFAULT NULL',
        'emergency_contact_name' => 'VARCHAR(100) DEFAULT NULL',
        'emergency_contact_phone'=> 'VARCHAR(50) DEFAULT NULL',
        'employment_start_date'  => 'DATE DEFAULT NULL',
        'employment_end_date'    => 'DATE DEFAULT NULL',
        'employment_status'      => 'VARCHAR(30) DEFAULT "Full-time"',
        'npi'                    => 'VARCHAR(20) DEFAULT NULL',
        'license_number'         => 'VARCHAR(50) DEFAULT NULL',
        'license_state'          => 'VARCHAR(20) DEFAULT NULL',
        'dea_number'             => 'VARCHAR(50) DEFAULT NULL',
        'education_credentials'  => 'VARCHAR(255) DEFAULT NULL',
        'supervising_provider'   => 'VARCHAR(150) DEFAULT NULL',
        'care_team'              => 'VARCHAR(150) DEFAULT NULL',
        'default_visit_type'     => 'VARCHAR(50) DEFAULT "In-Person"',
        'timezone'               => 'VARCHAR(50) DEFAULT "America/New_York"',
        'two_factor_enabled'     => 'TINYINT(1) DEFAULT 0'
    ];
    foreach ($userColsToAdd as $cName => $cDef) {
        if (!in_array($cName, $userCols)) {
            $db->exec("ALTER TABLE users ADD COLUMN {$cName} {$cDef}");
        }
    }

    // ── Ensure facilities table has all required enterprise fields ──
    $facCols = array_column($db->query("SHOW COLUMNS FROM facilities")->fetchAll(), 'Field');
    $facColsToAdd = [
        'facility_type'    => "VARCHAR(60) DEFAULT 'Clinic'",
        'fax'              => 'VARCHAR(50) DEFAULT NULL',
        'address_line1'    => 'VARCHAR(255) DEFAULT NULL',
        'address_line2'    => 'VARCHAR(255) DEFAULT NULL',
        'postal_code'      => 'VARCHAR(30) DEFAULT NULL',
        'timezone'         => "VARCHAR(50) DEFAULT 'America/New_York'",
        'contact_person'   => 'VARCHAR(150) DEFAULT NULL',
    ];
    foreach ($facColsToAdd as $cName => $cDef) {
        if (!in_array($cName, $facCols)) {
            $db->exec("ALTER TABLE facilities ADD COLUMN {$cName} {$cDef}");
        }
    }

    // ── Ensure locations table has all required enterprise fields ──
    $locCols = array_column($db->query("SHOW COLUMNS FROM locations")->fetchAll(), 'Field');
    $locColsToAdd = [
        'place_of_service' => "VARCHAR(80) DEFAULT NULL",
    ];
    foreach ($locColsToAdd as $cName => $cDef) {
        if (!in_array($cName, $locCols)) {
            $db->exec("ALTER TABLE locations ADD COLUMN {$cName} {$cDef}");
        }
    }

    // ── Ensure custom_roles has role_code, role_type ──
    $roleCols = array_column($db->query("SHOW COLUMNS FROM custom_roles")->fetchAll(), 'Field');
    $roleColsToAdd = [
        'role_code' => "VARCHAR(50) DEFAULT NULL",
        'role_type' => "VARCHAR(50) DEFAULT 'Standard'",
    ];
    foreach ($roleColsToAdd as $cName => $cDef) {
        if (!in_array($cName, $roleCols)) {
            $db->exec("ALTER TABLE custom_roles ADD COLUMN {$cName} {$cDef}");
        }
    }

    // ── Unify Custom Role workflow: add the sidebar-permissions matrix column,
    // backfill role_type, and seed the 6 built-in security roles into custom_roles.
    // Scoped try/catch so a seeding hiccup can't abort every migration statement below it.
    try {
        $roleCols2 = array_column($db->query("SHOW COLUMNS FROM custom_roles")->fetchAll(), 'Field');
        if (!in_array('permissions_matrix', $roleCols2)) {
            $db->exec("ALTER TABLE custom_roles ADD COLUMN permissions_matrix JSON DEFAULT NULL AFTER permissions");
        }

        // Backfill the 4 pre-existing template rows (Medical Assistant, Front Desk, Billing Staff, Practice Manager)
        $db->exec("UPDATE custom_roles SET role_type = 'template' WHERE role_type = 'Standard'");

        $fullCrud = ['view' => true, 'create' => true, 'edit' => true, 'delete' => true];
        $viewEdit = ['view' => true, 'create' => true, 'edit' => true, 'delete' => false];
        $viewOnly = ['view' => true, 'create' => false, 'edit' => false, 'delete' => false];
        $none     = ['view' => false, 'create' => false, 'edit' => false, 'delete' => false];
        $billingViewCreate = ['view' => true, 'create' => true, 'edit' => false, 'delete' => false];

        // Direct translation of the DEFAULT_ROLE_PERMS object in public/js/app.js (~line 17661)
        $securityMatrices = [
            'Super Admin' => [
                'dashboard' => $fullCrud, 'calendar' => $fullCrud, 'patients' => $fullCrud, 'telehealth' => $fullCrud,
                'messaging' => $fullCrud, 'billing' => $fullCrud, 'referrals' => $fullCrud, 'recalls' => $fullCrud,
                'reports' => $fullCrud, 'settings' => $fullCrud,
                'admin_facility' => $fullCrud, 'admin_specialties' => $fullCrud, 'admin_users' => $fullCrud, 'admin_roles' => $fullCrud
            ],
            'Doctor' => [
                'dashboard' => $viewEdit, 'calendar' => $fullCrud, 'patients' => $fullCrud, 'telehealth' => $fullCrud,
                'messaging' => $fullCrud, 'billing' => $billingViewCreate, 'referrals' => $fullCrud, 'recalls' => $viewEdit,
                'reports' => $viewOnly, 'settings' => $none,
                'admin_facility' => $none, 'admin_specialties' => $none, 'admin_users' => $none, 'admin_roles' => $none
            ],
            'Therapist' => [
                'dashboard' => $viewOnly, 'calendar' => $viewEdit, 'patients' => $viewEdit, 'telehealth' => $fullCrud,
                'messaging' => $viewEdit, 'billing' => $none, 'referrals' => $viewEdit, 'recalls' => $viewOnly,
                'reports' => $none, 'settings' => $none,
                'admin_facility' => $none, 'admin_specialties' => $none, 'admin_users' => $none, 'admin_roles' => $none
            ],
            'Nurse' => [
                'dashboard' => $viewOnly, 'calendar' => $viewEdit, 'patients' => $viewEdit, 'telehealth' => $viewEdit,
                'messaging' => $viewEdit, 'billing' => $none, 'referrals' => $viewEdit, 'recalls' => $viewEdit,
                'reports' => $viewOnly, 'settings' => $none,
                'admin_facility' => $none, 'admin_specialties' => $none, 'admin_users' => $none, 'admin_roles' => $none
            ],
            // Matches DEFAULT_ROLE_PERMS['Front Desk'] in app.js — same role, ENUM-correct name.
            'Receptionist' => [
                'dashboard' => $viewOnly, 'calendar' => $fullCrud, 'patients' => $viewEdit, 'telehealth' => $viewOnly,
                'messaging' => $viewEdit, 'billing' => $billingViewCreate, 'referrals' => $viewEdit, 'recalls' => $viewEdit,
                'reports' => $none, 'settings' => $none,
                'admin_facility' => $none, 'admin_specialties' => $none, 'admin_users' => $none, 'admin_roles' => $none
            ],
            'Billing Staff' => [
                'dashboard' => $viewOnly, 'calendar' => $viewOnly, 'patients' => $viewOnly, 'telehealth' => $none,
                'messaging' => $viewEdit, 'billing' => $fullCrud, 'referrals' => $none, 'recalls' => $none,
                'reports' => $viewEdit, 'settings' => $none,
                'admin_facility' => $none, 'admin_specialties' => $none, 'admin_users' => $none, 'admin_roles' => $none
            ],
        ];

        $roleIcons = [
            'Super Admin' => 'fas fa-user-shield', 'Doctor' => 'fas fa-user-md', 'Therapist' => 'fas fa-hands-holding-child',
            'Nurse' => 'fas fa-user-nurse', 'Receptionist' => 'fas fa-user-clock', 'Billing Staff' => 'fas fa-file-invoice-dollar'
        ];

        foreach ($securityMatrices as $roleName => $matrix) {
            $matrixJson = json_encode($matrix);
            if ($roleName === 'Billing Staff') {
                // Already exists as a template row (seeded below) — upgrade it in place, never re-insert (name is UNIQUE).
                $existingBilling = $db->query("SELECT id FROM custom_roles WHERE name = " . $db->quote($roleName))->fetch();
                if ($existingBilling) {
                    $stmt = $db->prepare("UPDATE custom_roles SET role_type = 'both', permissions_matrix = ?, is_system = 1 WHERE name = ?");
                    $stmt->execute([$matrixJson, $roleName]);
                } else {
                    $stmt = $db->prepare("INSERT INTO custom_roles (name, description, icon, base_role, permissions, permissions_matrix, is_system, role_type) VALUES (?, ?, ?, ?, ?, ?, 1, 'both')");
                    $stmt->execute([$roleName, 'Built-in security role.', $roleIcons[$roleName], $roleName, json_encode([]), $matrixJson]);
                }
                continue;
            }

            $existing = $db->query("SELECT id FROM custom_roles WHERE name = " . $db->quote($roleName))->fetch();
            if ($existing) {
                $stmt = $db->prepare("UPDATE custom_roles SET role_type = 'security', permissions_matrix = ?, is_system = 1, base_role = ? WHERE name = ?");
                $stmt->execute([$matrixJson, $roleName, $roleName]);
            } else {
                $stmt = $db->prepare("INSERT INTO custom_roles (name, description, icon, base_role, permissions, permissions_matrix, is_system, role_type) VALUES (?, ?, ?, ?, ?, ?, 1, 'security')");
                $stmt->execute([$roleName, 'Built-in security role.', $roleIcons[$roleName], $roleName, json_encode([]), $matrixJson]);
            }
        }
    } catch (\Exception $roleSeedEx) {
        error_log('Custom role migration/seed notice: ' . $roleSeedEx->getMessage());
    }

    // ── Ensure users table has all enterprise provider fields ──
    $userCols2 = array_column($db->query("SHOW COLUMNS FROM users")->fetchAll(), 'Field');
    $userColsToAdd2 = [
        'user_type'            => "VARCHAR(50) DEFAULT 'Staff'",
        'title'                => 'VARCHAR(20) DEFAULT NULL',
        'middle_name'          => 'VARCHAR(80) DEFAULT NULL',
        'suffix'               => 'VARCHAR(20) DEFAULT NULL',
        'preferred_name'       => 'VARCHAR(100) DEFAULT NULL',
        'job_title'            => 'VARCHAR(100) DEFAULT NULL',
        'employment_type'      => "VARCHAR(30) DEFAULT 'Full-time'",
        'employee_id'          => 'VARCHAR(50) DEFAULT NULL',
        'work_phone'           => 'VARCHAR(50) DEFAULT NULL',
        'provider_type'        => 'VARCHAR(30) DEFAULT NULL',
        'taxonomy_code'        => 'VARCHAR(50) DEFAULT NULL',
        'specialty'            => 'VARCHAR(100) DEFAULT NULL',
        // Admin-issued credential workflow: admin sets the password directly and emails it;
        // the user is forced to change it on first login, and the temp password expires if unused.
        'must_change_password' => 'TINYINT(1) DEFAULT 0',
        'temp_password_expires'=> 'DATETIME DEFAULT NULL',
    ];
    foreach ($userColsToAdd2 as $cName => $cDef) {
        if (!in_array($cName, $userCols2)) {
            $db->exec("ALTER TABLE users ADD COLUMN {$cName} {$cDef}");
        }
    }

    // license_number now holds a JSON array of { state, number } (a provider can be licensed
    // in multiple states), so it needs more room than the original VARCHAR(50).
    $db->exec("ALTER TABLE users MODIFY COLUMN license_number TEXT DEFAULT NULL");

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
