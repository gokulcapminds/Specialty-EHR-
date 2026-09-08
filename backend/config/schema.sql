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
