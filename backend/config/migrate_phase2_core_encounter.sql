-- =============================================================
-- Phase 2 Core Clinical Encounter — Database Migration
-- Run once against the pf_ehr database.
-- Safe: all ALTER TABLE use ADD COLUMN IF NOT EXISTS
-- =============================================================

USE ehr_db;

-- ---------------------------------------------------------------
-- 1. Encounter lifecycle status
--    Draft ? In Progress ? Ready for Sign ? Signed ? Locked
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN encounter_status
        ENUM('draft','in_progress','ready_for_sign','signed','locked')
        NOT NULL DEFAULT 'in_progress'
        AFTER encounter_type;

-- ---------------------------------------------------------------
-- 2. Lock flag and timestamp
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN lock_state TINYINT(1) NOT NULL DEFAULT 0 AFTER encounter_status,
    ADD COLUMN locked_at DATETIME DEFAULT NULL AFTER lock_state;

-- ---------------------------------------------------------------
-- 3. Billing queue status (set automatically on sign/finalize)
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN billing_queue_status
        ENUM('not_ready','pending_review','reviewed','billed','exempt')
        NOT NULL DEFAULT 'not_ready'
        AFTER locked_at;

-- ---------------------------------------------------------------
-- 4. Pain Score (Universal Vitals 0-10)
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN vital_pain_score TINYINT UNSIGNED DEFAULT NULL AFTER vital_bmi;

-- ---------------------------------------------------------------
-- 5. Appointment reference for specialty/provider inheritance (ENC-CORE-002)
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN appointment_id INT DEFAULT NULL AFTER patient_id,
    ADD CONSTRAINT fk_cn_appointment
        FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------
-- 6. Visit Type & Encounter Mode (ENC-CORE-001)
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN visit_type VARCHAR(60) DEFAULT NULL AFTER appointment_id,
    ADD COLUMN encounter_mode
        ENUM('In-Person','Telehealth','Walk-In','Phone') NOT NULL DEFAULT 'In-Person'
        AFTER visit_type;

-- ---------------------------------------------------------------
-- 7. Signed_by user FK (programmatic RBAC — keep signed_by_name for display)
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN signed_by_user_id INT DEFAULT NULL AFTER signed_signature_data,
    ADD CONSTRAINT fk_cn_signed_by
        FOREIGN KEY (signed_by_user_id) REFERENCES users(id) ON DELETE SET NULL;

-- ---------------------------------------------------------------
-- 8. Addendums JSON array (post-lock corrections — original preserved)
--    Each element: { id, user_id, user_name, user_role, timestamp, note, sig_data }
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD COLUMN addendums JSON DEFAULT NULL AFTER signed_signature_data;

-- ---------------------------------------------------------------
-- 9. Indexes for common status-based queries
-- ---------------------------------------------------------------
ALTER TABLE clinical_notes
    ADD INDEX idx_encounter_status (encounter_status),
    ADD INDEX idx_lock_state (lock_state),
    ADD INDEX idx_billing_queue (billing_queue_status),
    ADD INDEX idx_appointment (appointment_id);
