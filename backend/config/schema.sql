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
    scheduled_start DATETIME DEFAULT NULL,
    scheduled_end DATETIME DEFAULT NULL,
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
-- Seed demo users. Their initial passwords are intentionally NOT documented here: set a new password for every seeded account
-- (Administration > User Management) before using the system.
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

-- Migration 2026-09-30: billing payments ledger (applied live)
-- invoices / invoice_line_items were MyISAM; converted to InnoDB so money records are transactional and can carry foreign keys.
ALTER TABLE invoices ENGINE=InnoDB;
ALTER TABLE invoice_line_items ENGINE=InnoDB;
ALTER TABLE invoices ADD COLUMN billing_type ENUM('Self Pay','Insurance') NOT NULL DEFAULT 'Self Pay' AFTER status;
-- Every payment is a row; rows are voided (voided_at), never deleted. invoices.paid_amount = SUM(non-void payments).
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    patient_id INT NOT NULL,
    claim_id INT DEFAULT NULL,
    source ENUM('Patient','Primary Insurance','Secondary Insurance') NOT NULL DEFAULT 'Patient',
    method ENUM('Cash','Credit Card','Debit Card','Check','ACH','Other') NOT NULL DEFAULT 'Other',
    reference_no VARCHAR(100) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    paid_at DATE NOT NULL,
    notes TEXT DEFAULT NULL,
    received_by INT DEFAULT NULL,
    voided_at DATETIME DEFAULT NULL,
    voided_by INT DEFAULT NULL,
    void_reason VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pay_invoice (invoice_id),
    INDEX idx_pay_patient (patient_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS adjustments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    claim_id INT DEFAULT NULL,
    type ENUM('Contractual','Write-off','Discount') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    voided_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_adj_invoice (invoice_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Existing invoices that only had a running paid_amount were backfilled with one 'Other' payment row each.

-- Migration 2026-09-30: insurance claim workflow (applied live). billing_claims (legacy, unused, 0 rows) is superseded by insurance_claims.
CREATE TABLE IF NOT EXISTS insurance_payers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    payer_id_code VARCHAR(30) DEFAULT NULL,          -- clinic must enter the real payer ID before submitting
    claim_filing_code VARCHAR(4) NOT NULL DEFAULT 'CI', -- 837 SBR09: CI commercial, MB Medicare Part B, MC Medicaid
    phone VARCHAR(40) DEFAULT NULL,
    claim_address VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
-- seeded: Blue Cross Blue Shield, Medicare Part B (MB), Medicaid (MC), Aetna, UnitedHealthcare, Cigna Health, Humana, Kaiser Permanente
CREATE TABLE IF NOT EXISTS insurance_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_number VARCHAR(20) DEFAULT NULL UNIQUE,
    invoice_id INT NOT NULL,
    patient_id INT NOT NULL,
    encounter_id INT DEFAULT NULL,
    patient_insurance_id INT DEFAULT NULL,
    parent_claim_id INT DEFAULT NULL,                -- secondary claim -> the primary it was billed after
    sequence ENUM('Primary','Secondary') NOT NULL DEFAULT 'Primary',
    status ENUM('Draft','Ready','Submitted','Accepted','Rejected','Partially Paid','Paid','Denied','Appealed','Closed') NOT NULL DEFAULT 'Draft',
    payer_name VARCHAR(150) NOT NULL,
    payer_id_code VARCHAR(30) DEFAULT NULL,
    coverage_json TEXT DEFAULT NULL,                 -- snapshot of the patient_insurance row at claim creation
    date_of_service DATE NOT NULL,
    billed_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowed_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    insurance_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    adjustment_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    patient_resp DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    submitted_at DATETIME DEFAULT NULL,
    submission_ref VARCHAR(100) DEFAULT NULL,
    payer_claim_no VARCHAR(100) DEFAULT NULL,
    denial_code VARCHAR(20) DEFAULT NULL,
    denial_reason VARCHAR(255) DEFAULT NULL,
    appeal_note TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    timely_filing_due DATE DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_claim_invoice (invoice_id),
    INDEX idx_claim_patient (patient_id),
    INDEX idx_claim_status (status),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE RESTRICT,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS claim_lines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    invoice_line_item_id INT DEFAULT NULL,
    cpt_code VARCHAR(10) DEFAULT NULL,
    cpt_description VARCHAR(255) DEFAULT NULL,
    modifiers VARCHAR(20) DEFAULT NULL,
    icd10_code VARCHAR(15) DEFAULT NULL,
    dx_pointer VARCHAR(4) DEFAULT NULL,
    units INT NOT NULL DEFAULT 1,
    charge DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    allowed DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    adjustment DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    patient_resp DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    denial_code VARCHAR(20) DEFAULT NULL,
    adjudicated TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_cl_claim (claim_id),
    FOREIGN KEY (claim_id) REFERENCES insurance_claims(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS claim_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT NOT NULL,
    from_status VARCHAR(20) DEFAULT NULL,
    to_status VARCHAR(20) NOT NULL,
    note VARCHAR(500) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_csh_claim (claim_id),
    FOREIGN KEY (claim_id) REFERENCES insurance_claims(id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- system_settings keys (blank until the clinic supplies them, needed for 837P export): billing_submitter_id, billing_receiver_id, billing_contact_name, billing_contact_phone

-- Migration 2026-09-30: clearinghouse flow (eligibility 270/271, 837, 999/277CA, 835) with a built-in simulator (applied live)
CREATE TABLE IF NOT EXISTS edi_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_id INT DEFAULT NULL,
    patient_id INT NOT NULL,
    type ENUM('270','271','837','999','277CA','835') NOT NULL,
    direction ENUM('out','in') NOT NULL,
    control_number VARCHAR(20) DEFAULT NULL,
    status ENUM('created','sent','received','rejected','posted') NOT NULL DEFAULT 'created',
    content_encrypted MEDIUMTEXT DEFAULT NULL,   -- X12 text contains PHI: stored via EncryptionService::encrypt()
    summary_json TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_edi_claim (claim_id),
    INDEX idx_edi_patient (patient_id),
    INDEX idx_edi_type (type)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS eligibility_checks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    claim_id INT DEFAULT NULL,
    patient_insurance_id INT DEFAULT NULL,
    sequence ENUM('Primary','Secondary') NOT NULL DEFAULT 'Primary',
    status ENUM('Active','Inactive','Error') NOT NULL,
    plan_name VARCHAR(150) DEFAULT NULL,
    copay DECIMAL(10,2) DEFAULT NULL,
    deductible DECIMAL(10,2) DEFAULT NULL,
    deductible_met DECIMAL(10,2) DEFAULT NULL,
    coinsurance_pct DECIMAL(5,2) DEFAULT NULL,
    message VARCHAR(255) DEFAULT NULL,
    request_tx_id INT DEFAULT NULL,
    response_tx_id INT DEFAULT NULL,
    checked_by INT DEFAULT NULL,
    checked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_elig_patient (patient_id),
    INDEX idx_elig_claim (claim_id)
) ENGINE=InnoDB;
ALTER TABLE insurance_claims ADD COLUMN clearinghouse_ref VARCHAR(60) DEFAULT NULL AFTER submission_ref;
ALTER TABLE insurance_claims ADD COLUMN validated_at DATETIME DEFAULT NULL AFTER clearinghouse_ref;
ALTER TABLE insurance_claims ADD COLUMN validation_json TEXT DEFAULT NULL AFTER validated_at;
ALTER TABLE insurance_claims ADD COLUMN pending_835_tx_id INT DEFAULT NULL AFTER validation_json;
-- system_settings key: clearinghouse_mode = 'simulator' ('availity' reserved, not implemented)

-- Migration 2026-09-30: Encounters module - one encounter per appointment (applied live)
-- Replaces the plain idx_appointment index; NULLs stay allowed (walk-ins / chart-created notes have no appointment).
ALTER TABLE clinical_notes DROP INDEX idx_appointment, ADD UNIQUE KEY uniq_note_appointment (appointment_id);

-- Migration 2026-10-01: Telehealth timed join (applied live)
-- Patients may join 5 min before scheduled_start until scheduled_end; NULL = ad-hoc session (ungated). Backfilled from appointments.
ALTER TABLE telehealth_sessions ADD COLUMN scheduled_start DATETIME DEFAULT NULL AFTER status, ADD COLUMN scheduled_end DATETIME DEFAULT NULL AFTER scheduled_start;

-- Migration 2026-10-01: Provider Availability - providers set when they ARE available ('In Office'); other categories are time off
ALTER TABLE provider_time_blocks ADD COLUMN category VARCHAR(20) NOT NULL DEFAULT 'Out Of Office' AFTER weekday;
ALTER TABLE provider_time_blocks ADD COLUMN facility_id INT NULL AFTER category;

-- Migration 2026-10-03: Audit log is now tamper-evident and append-only (applied live; the old 3,223 rows were deleted on purpose - the old chain was unverifiable)
-- * foreign keys removed: ON DELETE SET NULL silently rewrote audit rows when a user/patient was deleted (ids are kept as plain integers)
-- * timestamp is a UTC DATETIME written by the logger (UTC_TIMESTAMP()); prev_hash links each row to the one before it
-- * log_hash is now HMAC-SHA256 (key derived from the encryption key) over a canonical JSON of the row - see App\Services\AuditLogger
-- * audit_chain_head (1 row, locked FOR UPDATE by the logger) serialises writers and lets verification detect deleted tail rows
-- * BEFORE UPDATE / BEFORE DELETE triggers reject any change to audit_logs (TRUNCATE/DROP TRIGGER by root are NOT blocked - see CLAUDE.md)
TRUNCATE TABLE audit_logs;
ALTER TABLE audit_logs DROP FOREIGN KEY audit_logs_ibfk_1;
ALTER TABLE audit_logs DROP FOREIGN KEY audit_logs_ibfk_2;
ALTER TABLE audit_logs MODIFY `timestamp` DATETIME NOT NULL, ADD COLUMN prev_hash CHAR(64) NOT NULL AFTER `timestamp`;
ALTER TABLE audit_logs ADD INDEX idx_audit_patient (patient_id, id), ADD INDEX idx_audit_username (username), ADD INDEX idx_audit_module (target_module), ADD INDEX idx_audit_time (`timestamp`);
CREATE TABLE IF NOT EXISTS audit_chain_head (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    last_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
    last_hash CHAR(64) NOT NULL,
    entry_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    head_mac CHAR(64) NOT NULL
) ENGINE=InnoDB;
-- seed row (last_hash = 64 zeros, head_mac = AuditLogger::headMac(0, zeros, 0)) is inserted by the one-off migration script, because the MAC needs the PHP key:
-- INSERT INTO audit_chain_head (id, last_id, last_hash, entry_count, head_mac) VALUES (1, 0, REPEAT('0',64), 0, '<AuditLogger::headMac(0, GENESIS, 0)>');
CREATE TRIGGER audit_logs_block_update BEFORE UPDATE ON audit_logs FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only: rows cannot be changed';
CREATE TRIGGER audit_logs_block_delete BEFORE DELETE ON audit_logs FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only: rows cannot be deleted';

-- Migration 2026-10-03: the Therapist role was removed (applied live). Roles are now Super Admin, Doctor, Nurse, Receptionist, Billing Staff (+ Patient reserved).
-- The seed user 'therapist_jane' above (never logged in, no data) was deleted; the Therapist rows in custom_roles / rbac_policies were removed.
-- Access rules live in backend/app/Security/Roles.php (not in the database).
DELETE FROM users WHERE role = 'Therapist';
DELETE FROM custom_roles WHERE name = 'Therapist' OR base_role = 'Therapist';
DELETE FROM rbac_policies WHERE role = 'Therapist';
ALTER TABLE users MODIFY role ENUM('Super Admin','Doctor','Nurse','Receptionist','Billing Staff','Patient') NOT NULL;
-- one spelling for the user type chosen in the wizard
UPDATE users SET user_type = 'Staff Member' WHERE user_type = 'Staff';
ALTER TABLE users ALTER COLUMN user_type SET DEFAULT 'Staff Member';

-- ===== Roles & permissions: one source of truth (App\Security\Roles::AREAS) =====
-- Removed the stored-but-never-enforced permission systems: custom roles / templates / matrices, the rbac_policies
-- table and the per-user Step-4 tick list. Access is exactly the user's role; the Roles & Permissions tab is read-only
-- and GET /api/me returns the derived `permissions`.
DROP TABLE IF EXISTS custom_roles;
DROP TABLE IF EXISTS rbac_policies;
ALTER TABLE users DROP COLUMN custom_permissions;

-- ===== Custom roles (enforced): role = base role (ceiling) + per-area View/Create/Edit/Delete ticks =====
-- Effective access = base role's access AND the ticks (App\Security\Roles::customAllows + RouteAreas, checked in AuthenticationMiddleware).
CREATE TABLE IF NOT EXISTS custom_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    base_role ENUM('Doctor','Nurse','Receptionist','Billing Staff') NOT NULL,
    permissions_matrix JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_custom_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ALTER TABLE users ADD COLUMN custom_role_id INT NULL DEFAULT NULL;

-- ===== Login hardening (Oct 2026): lockout, password-change session invalidation, password reset by email =====
ALTER TABLE users ADD COLUMN failed_login_count INT NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN locked_until DATETIME NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN password_changed_at DATETIME NULL DEFAULT NULL;
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,             -- SHA-256 of the emailed token; the token itself is never stored
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    requested_ip VARCHAR(45) NULL,
    UNIQUE KEY uniq_reset_token (token_hash),
    KEY idx_reset_user (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- The seeded demo accounts dr_smith and billing_staff were forced to change their (previously published) default passwords:
UPDATE users SET must_change_password = 1, temp_password_expires = NULL WHERE username IN ('dr_smith', 'billing_staff');

-- ===== Cardiology visit types (Oct 2026): one shared list in App\Support\VisitTypes =====
-- Old free-text values were mapped to the closest current type (other values, e.g. 'Stress Test', were left as they are).
UPDATE appointments SET visit_type = 'New Patient Consultation' WHERE visit_type = 'New Patient';
UPDATE appointments SET visit_type = 'Follow-Up Visit' WHERE visit_type IN ('Follow Up', 'Follow-up');
UPDATE appointments SET visit_type = 'Cardiology Consultation' WHERE visit_type = 'Cardiology Consult';
UPDATE clinical_notes SET visit_type = 'New Patient Consultation' WHERE visit_type = 'New Patient';
UPDATE clinical_notes SET visit_type = 'Follow-Up Visit' WHERE visit_type IN ('Follow Up', 'Follow-up');
UPDATE clinical_notes SET visit_type = 'Cardiology Consultation' WHERE visit_type = 'Cardiology Consult';

-- ===== Cardiology encounter workflow C01-C14 (Oct 2026), Phase 1 =====
-- C01 Visit Details: referring provider + reason for referral. C05: medication-reconciliation stamp.
ALTER TABLE clinical_notes ADD COLUMN referring_provider VARCHAR(150) NULL DEFAULT NULL;
ALTER TABLE clinical_notes ADD COLUMN referral_reason VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE clinical_notes ADD COLUMN med_rec_done_at DATETIME NULL DEFAULT NULL;
-- C04 Cardiac History: one row per patient. Holds the inputs the cardiac risk scores need
-- (ASCVD, CHA2DS2-VASc, HAS-BLED) so they are reviewed each visit, never retyped.
CREATE TABLE IF NOT EXISTS patient_cardiac_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hypertension TINYINT(1) NOT NULL DEFAULT 0,
    diabetes TINYINT(1) NOT NULL DEFAULT 0,
    dyslipidemia TINYINT(1) NOT NULL DEFAULT 0,
    smoking_status ENUM('Never','Former','Current') NOT NULL DEFAULT 'Never',
    pack_years DECIMAL(5,1) NULL DEFAULT NULL,
    obesity TINYINT(1) NOT NULL DEFAULT 0,
    ckd TINYINT(1) NOT NULL DEFAULT 0,
    sleep_apnea TINYINT(1) NOT NULL DEFAULT 0,
    family_premature_cad TINYINT(1) NOT NULL DEFAULT 0,
    cad TINYINT(1) NOT NULL DEFAULT 0,
    prior_mi TINYINT(1) NOT NULL DEFAULT 0,
    prior_mi_date DATE NULL DEFAULT NULL,
    prior_pci TINYINT(1) NOT NULL DEFAULT 0,
    prior_pci_date DATE NULL DEFAULT NULL,
    prior_cabg TINYINT(1) NOT NULL DEFAULT 0,
    prior_cabg_date DATE NULL DEFAULT NULL,
    heart_failure TINYINT(1) NOT NULL DEFAULT 0,
    hf_type ENUM('HFrEF','HFmrEF','HFpEF') NULL DEFAULT NULL,
    atrial_fibrillation TINYINT(1) NOT NULL DEFAULT 0,
    valve_disease TINYINT(1) NOT NULL DEFAULT 0,
    cardiomyopathy TINYINT(1) NOT NULL DEFAULT 0,
    pad TINYINT(1) NOT NULL DEFAULT 0,
    stroke_tia TINYINT(1) NOT NULL DEFAULT 0,
    device_type VARCHAR(60) NULL DEFAULT NULL,
    device_implant_date DATE NULL DEFAULT NULL,
    prior_bleeding TINYINT(1) NOT NULL DEFAULT 0,
    labile_inr TINYINT(1) NOT NULL DEFAULT 0,
    alcohol_excess TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL DEFAULT NULL,
    last_reviewed_at DATETIME NULL DEFAULT NULL,
    last_reviewed_by INT NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_patient_cardiac (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Migration 2026-10-06: facility-based data isolation (applied live). Every facility previously shared
-- one pool of patients/appointments/encounters/billing/etc; this backfills the two tables that carry
-- facility_id (patients, users - every other patient-linked table has no facility_id of its own and is
-- scoped at query time via a join back to patients) so the new per-request scoping in the controllers
-- has a real value to filter on. All 15 patients and 4 users with facility_id IS NULL were defaulted to
-- facility 11 (Metro Heart & Vascular Center) as a one-time data backfill, not a schema change - this is
-- demo/test data, not real PHI, so a single default was acceptable; a real deployment migrating existing
-- data would need per-record review instead of one default for everything.
UPDATE patients SET facility_id = 11 WHERE facility_id IS NULL;
UPDATE users SET facility_id = 11 WHERE facility_id IS NULL;

-- Migration 2026-10-07: patient_ids_json was a plain TEXT column (64KB limit), but
-- createPatientIdRowElement() (app.js) stores each attached ID document (driver's license photo,
-- passport scan, etc.) as a base64 data URL inside this same JSON column - any real attachment exceeds
-- 64KB. With sql_mode empty (non-strict), MySQL silently truncated the value instead of erroring, which
-- corrupted the JSON and made the attached document vanish on the next load (reproduced: a 203KB payload
-- was silently cut to exactly 65,535 bytes, then failed to json_decode). Widened to LONGTEXT (matches
-- photo_url, which already got this same fix previously). Found via manual testing of the patient wizard's
-- "Government & Clinical Identification Documents" step.
ALTER TABLE patients MODIFY patient_ids_json LONGTEXT;
