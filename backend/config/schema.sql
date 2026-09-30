CREATE DATABASE IF NOT EXISTS pf_ehr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pf_ehr;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff', 'Patient') NOT NULL,
    theme_preference ENUM('light', 'dark', 'classic', 'high-contrast') DEFAULT 'light',
    is_active TINYINT(1) DEFAULT 1,
    must_change_password TINYINT(1) DEFAULT 0,
    temp_password_expires DATETIME DEFAULT NULL,
    remember_token_hash VARCHAR(64) DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Patients Table
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name_encrypted VARCHAR(255) NOT NULL,
    last_name_encrypted VARCHAR(255) NOT NULL,
    dob_encrypted VARCHAR(255) NOT NULL,
    ssn_encrypted VARCHAR(255) DEFAULT NULL,
    email VARCHAR(100) DEFAULT NULL,
    phone_encrypted VARCHAR(255) DEFAULT NULL,
    address_encrypted TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Appointments Table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    status ENUM('Scheduled', 'Checked-In', 'In-Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_times (start_time, end_time)
) ENGINE=InnoDB;

-- Clinical Notes Table
CREATE TABLE IF NOT EXISTS clinical_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    note_date DATETIME NOT NULL,
    
    -- Encounter General Metadata
    encounter_type ENUM('General', 'Pediatrics', 'OB/GYN') NOT NULL DEFAULT 'General',
    
    -- General Vitals
    vital_temp DECIMAL(4,1) DEFAULT NULL,
    vital_bp_systolic INT DEFAULT NULL,
    vital_bp_diastolic INT DEFAULT NULL,
    vital_heart_rate INT DEFAULT NULL,
    vital_resp_rate INT DEFAULT NULL,
    vital_spo2 INT DEFAULT NULL,
    vital_height DECIMAL(5,2) DEFAULT NULL,
    vital_weight DECIMAL(5,2) DEFAULT NULL,
    vital_bmi DECIMAL(4,1) DEFAULT NULL,
    
    -- SOAP Fields
    chief_complaint TEXT DEFAULT NULL,
    hpi TEXT DEFAULT NULL,
    ros TEXT DEFAULT NULL,
    
    -- Physical Examination
    pe_general TEXT DEFAULT NULL,
    pe_heent TEXT DEFAULT NULL,
    pe_cardio TEXT DEFAULT NULL,
    pe_resp TEXT DEFAULT NULL,
    pe_abdomen TEXT DEFAULT NULL,
    pe_neuro TEXT DEFAULT NULL,
    pe_skin TEXT DEFAULT NULL,
    
    -- Pediatrics Specific
    growth_weight_percentile INT DEFAULT NULL,
    growth_height_percentile INT DEFAULT NULL,
    immunizations_administered JSON DEFAULT NULL,
    
    -- OB/GYN Specific
    obgyn_lmp DATE DEFAULT NULL,
    obgyn_edd DATE DEFAULT NULL,
    obgyn_gravida INT DEFAULT NULL,
    obgyn_para INT DEFAULT NULL,
    obgyn_abortions INT DEFAULT NULL,
    obgyn_living INT DEFAULT NULL,
    obgyn_fundal_height DECIMAL(4,1) DEFAULT NULL,
    obgyn_fetal_heart_rate INT DEFAULT NULL,
    
    -- Assessment & Plan
    clinical_summary TEXT DEFAULT NULL,
    
    -- Digital Signature / Attestation
    signed_by_name VARCHAR(100) DEFAULT NULL,
    signed_by_credentials VARCHAR(20) DEFAULT NULL,
    signed_at DATETIME DEFAULT NULL,
    signed_signature_data TEXT DEFAULT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Patient Documents Table
CREATE TABLE IF NOT EXISTS patient_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    stored_filename VARCHAR(64) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Secure Messages Table
-- Secure Messages Tables (Updated Architecture)
CREATE TABLE IF NOT EXISTS secure_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    sender_id INT NOT NULL,
    patient_id INT DEFAULT NULL,
    encounter_id INT DEFAULT NULL,
    priority ENUM('Normal', 'High', 'Urgent') DEFAULT 'Normal',
    category ENUM('Clinical', 'Appointment', 'Billing', 'Referral', 'Prescription', 'Lab Result', 'General') DEFAULT 'General',
    subject VARCHAR(255) NOT NULL,
    body_encrypted TEXT NOT NULL,
    status ENUM('Draft', 'Sent') DEFAULT 'Sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES secure_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    FOREIGN KEY (encounter_id) REFERENCES clinical_notes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS message_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    receiver_id INT NOT NULL,
    is_cc BOOLEAN DEFAULT FALSE,
    read_at DATETIME DEFAULT NULL,
    is_archived BOOLEAN DEFAULT FALSE,
    is_trashed BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (message_id) REFERENCES secure_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS message_attachments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES secure_messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Billing Claims Table
CREATE TABLE IF NOT EXISTS billing_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    created_by INT NOT NULL,
    date_of_service DATE NOT NULL,
    icd10_code VARCHAR(15) NOT NULL,
    cpt_code VARCHAR(10) NOT NULL,
    charged_amount DECIMAL(10,2) NOT NULL,
    paid_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('Draft', 'Submitted', 'Paid', 'Denied', 'Appealed') DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Telehealth Sessions Table
CREATE TABLE IF NOT EXISTS telehealth_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_id INT DEFAULT NULL,
    created_by INT NOT NULL,
    room_name VARCHAR(120) NOT NULL UNIQUE,
    patient_email VARCHAR(150) NOT NULL,
    join_url VARCHAR(255) NOT NULL,
    jitsi_url VARCHAR(255) NOT NULL,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Immutable Audit Log Table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    username VARCHAR(50) DEFAULT NULL,
    user_role VARCHAR(50) DEFAULT NULL,
    patient_id INT DEFAULT NULL,
    action_type VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    target_module VARCHAR(50) NOT NULL,
    record_id VARCHAR(50) DEFAULT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    log_hash CHAR(64) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    INDEX idx_audit (timestamp, action_type)
) ENGINE=InnoDB;

-- Seed default users with secure password hashes
-- Default passwords:
-- admin / AdminPassword123!
-- doctor / DoctorPassword123!
-- therapist / TherapistPassword123!
-- billing / BillingPassword123!
-- front_desk / FrontDeskPassword123!
INSERT INTO users (username, password_hash, first_name, last_name, email, role, theme_preference) VALUES
('admin', '$2y$10$QfFvTum.HlOmRKtDt5plXerArLDkq.yIUP5bI/.bNZ51PqSHCMN1K', 'System', 'Admin', 'admin@bhevariol.health', 'Super Admin', 'dark'),
('dr_smith', '$2y$10$9ZsFoTAY7.PDaBUhysaV7Od63hkCe5Ftq8rmyEnCxu2miGSwoeh5y', 'John', 'Smith', 'smith@bhevariol.health', 'Doctor', 'light'),
('therapist_jane', '$2y$10$xznqJ.K94W8Se1tBCq0DUOwqWKtelBRNuN4I84Va9DaJdRlp6hYam', 'Jane', 'Doe', 'jane@bhevariol.health', 'Therapist', 'light'),
('billing_staff', '$2y$10$b0W.kub8qC1jsodXi77QT.i23GsExUF0a2K4CESwn5z9jM1awq8SO', 'Bob', 'Johnson', 'bob@bhevariol.health', 'Billing Staff', 'classic'),
('front_desk', '$2y$10$oZ1uXVXYOSgnKVENh8abneqdM97XPR/2pCbyV4.MfH8E2KWDj7DiG', 'Sarah', 'Miller', 'frontdesk@bhevariol.health', 'Receptionist', 'light');

-- ==========================================================================
-- Migration: Expanded Patient Registration (Facility/Status/Emergency
-- Contacts/Insurance type). Run once against an existing database; these
-- statements are not idempotent (columns/tables added elsewhere already
-- diverged from the CREATE TABLE blocks above).
-- ==========================================================================
ALTER TABLE patients
    ADD COLUMN facility_id INT NULL AFTER primary_provider_id,
    ADD COLUMN patient_status ENUM('Active','Inactive','Deceased','Merged') NOT NULL DEFAULT 'Active',
    ADD COLUMN communication_consent TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN emergency_contacts_json TEXT NULL,
    ADD CONSTRAINT fk_patients_facility FOREIGN KEY (facility_id) REFERENCES facilities(id) ON DELETE SET NULL;

ALTER TABLE patient_insurance
    ADD COLUMN insurance_type ENUM('Primary','Secondary') NOT NULL DEFAULT 'Primary',
    ADD COLUMN member_id VARCHAR(100) NULL AFTER primary_provider,
    ADD COLUMN subscriber_first_name VARCHAR(100) NULL AFTER subscriber_name,
    ADD COLUMN subscriber_last_name VARCHAR(100) NULL AFTER subscriber_first_name,
    ADD COLUMN subscriber_employer VARCHAR(150) NULL,
    ADD UNIQUE KEY uniq_patient_insurance_type (patient_id, insurance_type);

-- Migration: wire up the "Declined to specify" checkboxes for Gender
-- Identity, Language, Race, and Ethnicity (only Sexual Orientation had a
-- backing column before this).
ALTER TABLE patients
    ADD COLUMN gender_identity_declined TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN language_declined TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN race_declined TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN ethnicity_declined TINYINT(1) NOT NULL DEFAULT 0;

-- Migration: duplicate-patient (MPI) detection. first_name_encrypted/
-- last_name_encrypted/dob_encrypted use a random IV per write, so identical
-- plaintext never matches on WHERE; patient_match_hash is a deterministic
-- HMAC of normalized name+DOB (see EncryptionService::blindIndex) used only
-- to detect an existing match, never to recover the plaintext.
ALTER TABLE patients
    ADD COLUMN patient_match_hash CHAR(64) NULL AFTER dob_encrypted,
    ADD INDEX idx_patient_match_hash (patient_match_hash);

-- Migration: structured allergy list. Previously allergies only existed as
-- free text on clinical_notes.allergies with no persistent, queryable record.
CREATE TABLE patient_allergies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    allergen VARCHAR(255) NOT NULL,
    category ENUM('Medication','Food','Environmental','Other') NOT NULL DEFAULT 'Medication',
    reaction VARCHAR(255) NULL,
    severity ENUM('Mild','Moderate','Severe','Life-Threatening') NOT NULL DEFAULT 'Moderate',
    onset_date DATE NULL,
    status ENUM('Active','Inactive','Resolved') NOT NULL DEFAULT 'Active',
    notes TEXT NULL,
    recorded_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_patient_allergies_patient (patient_id)
);

-- Migration: the Cardiology-EHR encounter workflow build. clinical_notes accumulated a large
-- number of live columns never reflected here (this file lagged the actual DB well before this
-- migration — see the file-level note at the top). encounter_type in particular drifted from
-- ENUM('General','Pediatrics','OB/GYN') to a free-text specialty name (VARCHAR), since the
-- encounter workflow now supports 7 specialties (Cardiology, Orthopedics, Dermatology,
-- Neurology, Oncology, Ophthalmology, Physical Therapy) plus the original visit types — not
-- worth re-enumerating as an ENUM given how often specialties get added/renamed via the
-- Specialty Management admin screen (see the `specialties` table below).
ALTER TABLE clinical_notes
    MODIFY COLUMN encounter_type VARCHAR(100) NOT NULL DEFAULT 'Cardiology',
    ADD COLUMN appointment_id INT NULL,
    ADD COLUMN visit_type VARCHAR(60) NULL,
    ADD COLUMN encounter_mode ENUM('In-Person','Telehealth','Walk-In','Phone') NOT NULL DEFAULT 'In-Person',
    ADD COLUMN encounter_status ENUM('draft','in_progress','ready_for_sign','signed','locked') NOT NULL DEFAULT 'in_progress',
    ADD COLUMN lock_state TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN locked_at DATETIME NULL,
    ADD COLUMN billing_queue_status ENUM('not_ready','pending_review','reviewed','billed','exempt') NOT NULL DEFAULT 'not_ready',
    ADD COLUMN specialty_data LONGTEXT NULL,
    ADD COLUMN vital_pain_score TINYINT UNSIGNED NULL,
    ADD COLUMN vital_pulse_pattern VARCHAR(50) NULL,
    ADD COLUMN vital_pulse_volume VARCHAR(50) NULL,
    ADD COLUMN allergies TEXT NULL,
    ADD COLUMN pmh TEXT NULL,
    ADD COLUMN current_medications TEXT NULL,
    ADD COLUMN icd10_codes TEXT NULL,
    ADD COLUMN family_history TEXT NULL,
    ADD COLUMN fm_assessment TEXT NULL,
    ADD COLUMN functional_assessment TEXT NULL,
    ADD COLUMN primary_care_data TEXT NULL,
    ADD COLUMN pediatric_data TEXT NULL,
    ADD COLUMN peds_newborn_data TEXT NULL,
    ADD COLUMN peds_one_month_data TEXT NULL,
    ADD COLUMN peds_two_month_data TEXT NULL,
    ADD COLUMN peds_four_month_data TEXT NULL,
    ADD COLUMN peds_six_month_data TEXT NULL,
    ADD COLUMN peds_nine_month_data TEXT NULL,
    ADD COLUMN peds_twelve_month_data TEXT NULL,
    ADD COLUMN peds_fifteen_month_data TEXT NULL,
    ADD COLUMN peds_eighteen_month_data TEXT NULL,
    ADD COLUMN obgyn_data TEXT NULL,
    ADD COLUMN addendums JSON NULL,
    ADD COLUMN signed_by_user_id INT NULL,
    -- The 7 Specialty EHR panels (public/modules/clinical_modal.php), one JSON blob column
    -- each, read/written generically via FormData(specialtyForm) — see CLAUDE.md.
    ADD COLUMN cardio_data TEXT NULL,
    ADD COLUMN ortho_data TEXT NULL,
    ADD COLUMN derma_data TEXT NULL,
    ADD COLUMN neuro_data TEXT NULL,
    ADD COLUMN onco_data TEXT NULL,
    ADD COLUMN ophthal_data TEXT NULL,
    ADD COLUMN pt_data TEXT NULL,
    -- growth_*_percentile and obgyn_fundal_height/obgyn_fetal_heart_rate drifted from the
    -- numeric types below to free text in the live DB (percentile bands like "50th-75th",
    -- fundal height/FHR notes that aren't always a clean number) — corrected here rather than
    -- re-adding the original numeric types, which no longer match what the app writes.
    MODIFY COLUMN growth_weight_percentile VARCHAR(20) NULL,
    MODIFY COLUMN growth_height_percentile VARCHAR(20) NULL,
    MODIFY COLUMN obgyn_fundal_height VARCHAR(50) NULL,
    MODIFY COLUMN obgyn_fetal_heart_rate VARCHAR(50) NULL;

-- Migration: Medication Review — the Medications domain (MedicationController), replacing the
-- old clinical_notes.current_medications free-text field for anything entered through the real
-- encounter/patient-chart flows. encounter_id links a prescription to the encounter it was
-- written during, when there is one.
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    encounter_id INT NULL,
    medication_name VARCHAR(255) NOT NULL,
    rxnorm_code VARCHAR(32) NULL,
    dosage VARCHAR(64) NOT NULL,
    route VARCHAR(64) NOT NULL,
    frequency VARCHAR(64) NOT NULL,
    quantity INT NULL,
    refills INT DEFAULT 0,
    refills_remaining INT DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    status ENUM('Active','Completed','Discontinued','On-Hold') DEFAULT 'Active',
    discontinue_reason VARCHAR(255) NULL,
    instructions TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_meds_patient_status (patient_id, status),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id)
);

-- Migration: Diagnosis domain (ProblemController) — this is the "Diagnosis" step of the
-- encounter's common flow, and the patient chart's problem list; replaces the old
-- clinical_notes.icd10_codes free-text field for anything entered through the real flows.
CREATE TABLE patient_problems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    icd10_code VARCHAR(16) NOT NULL,
    description VARCHAR(255) NOT NULL,
    onset_date DATE NULL,
    resolved_date DATE NULL,
    status ENUM('Active','Resolved','Inactive','Chronic') DEFAULT 'Active',
    chronicity ENUM('Acute','Chronic','Recurrent') DEFAULT 'Chronic',
    clinical_notes TEXT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_problems_patient_status (patient_id, status),
    KEY idx_problems_icd10 (icd10_code),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Migration: Orders/Diagnostics + Results Review domain (OrderController). order_number is a
-- generated ORD-NNNNN identifier; encounter_id links an order to the encounter it was placed
-- during. results is a child table (order_id) since one order can have multiple result entries
-- (e.g. a panel with several component values).
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(32) NOT NULL UNIQUE,
    patient_id INT NOT NULL,
    provider_id INT NOT NULL,
    encounter_id INT NULL,
    specialty VARCHAR(64) NOT NULL DEFAULT 'General',
    order_type ENUM('Lab','Imaging','Procedure','Referral','Prescription','Therapy') NOT NULL,
    order_code VARCHAR(64) NULL,
    order_name VARCHAR(255) NOT NULL,
    priority ENUM('Routine','Urgent','STAT') DEFAULT 'Routine',
    status ENUM('Draft','Submitted','In-Progress','Resulted','Reviewed','Cancelled') DEFAULT 'Submitted',
    clinical_indication TEXT NULL,
    special_instructions TEXT NULL,
    ordered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resulted_at DATETIME NULL,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_orders_patient_status (patient_id, status),
    KEY idx_orders_provider_status (provider_id, status),
    KEY idx_orders_type (order_type),
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES users(id)
);

CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    patient_id INT NOT NULL,
    result_type VARCHAR(64) NOT NULL,
    test_name VARCHAR(255) NOT NULL,
    result_value TEXT NOT NULL,
    units VARCHAR(32) NULL,
    reference_range VARCHAR(128) NULL,
    abnormal_flag ENUM('Normal','Abnormal','Critical_High','Critical_Low') DEFAULT 'Normal',
    status ENUM('Preliminary','Final','Corrected') DEFAULT 'Final',
    performing_lab VARCHAR(255) NULL,
    document_id INT NULL,
    provider_notes TEXT NULL,
    signed_by INT NULL,
    signed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_results_patient_flag (patient_id, abnormal_flag),
    KEY idx_results_order (order_id),
    KEY idx_results_signed (signed_by, signed_at),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
);

-- Migration: Specialty Management (SpecialtyController, Administration screen) — the list of
-- specialties the app supports, editable by a Super Admin rather than hardcoded. specialty_key
-- is the stable identifier used elsewhere; specialty_name is the display name.
CREATE TABLE specialties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    specialty_key VARCHAR(100) NOT NULL UNIQUE,
    specialty_name VARCHAR(150) NOT NULL,
    code VARCHAR(50) NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-stethoscope',
    description TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Migration 2026-09-29: calendar feature pack (cancel reasons, reminder tracking, provider blocked time)
ALTER TABLE appointments ADD COLUMN cancel_reason VARCHAR(255) NULL AFTER waiting_list_data;
ALTER TABLE appointments ADD COLUMN reminder_sent_at DATETIME NULL AFTER cancel_reason;
CREATE TABLE IF NOT EXISTS provider_time_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    provider_id INT NOT NULL,
    block_date DATE NULL,
    weekday TINYINT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    reason VARCHAR(255) NULL,
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_block_provider (provider_id, block_date, weekday),
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Migration 2026-09-30: automatic appointment reminders were removed; drop their tracking column
ALTER TABLE appointments DROP COLUMN reminder_sent_at;

-- Migration 2026-09-30: registration wizard drafts are stored as patients with status 'Draft'
ALTER TABLE patients MODIFY patient_status ENUM('Active','Inactive','Deceased','Merged','Draft') DEFAULT 'Active';
