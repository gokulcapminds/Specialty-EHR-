<?php
/**
 * Migration: Add imaging/DICOM metadata columns to patient_documents
 * and create imaging_ai_findings table.
 *
 * Run: php backend/migrations/add_imaging_columns.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
use App\Models\Database;

echo "Running imaging migration...\n";

// 1. Add columns one-by-one (MySQL < 8.0 doesn't support ADD COLUMN IF NOT EXISTS)
$alters = [
    "ALTER TABLE patient_documents ADD COLUMN modality VARCHAR(30) DEFAULT NULL COMMENT 'X-RAY,MRI,CT,ULTRASOUND,ECG,OTHER' AFTER mime_type",
    "ALTER TABLE patient_documents ADD COLUMN study_description VARCHAR(255) DEFAULT NULL AFTER modality",
    "ALTER TABLE patient_documents ADD COLUMN study_date DATE DEFAULT NULL AFTER study_description",
    "ALTER TABLE patient_documents ADD COLUMN ai_analysis_status ENUM('none','pending','completed','failed') NOT NULL DEFAULT 'none' AFTER study_date",
    "ALTER TABLE patient_documents ADD COLUMN ai_findings_json JSON DEFAULT NULL COMMENT 'Latest accepted AI analysis' AFTER ai_analysis_status",
];

foreach ($alters as $sql) {
    preg_match('/ADD COLUMN (\w+)/', $sql, $m);
    $col = $m[1] ?? '?';
    try {
        Database::query($sql, []);
        echo "✅ Column '$col' added.\n";
    } catch (Exception $e) {
        // 1060 = Duplicate column name — column already exists, skip
        if (strpos($e->getMessage(), '1060') !== false) {
            echo "ℹ️  Column '$col' already exists, skipped.\n";
        } else {
            echo "❌ Column '$col': " . $e->getMessage() . "\n";
        }
    }
}

// 2. Create imaging_ai_findings table (full audit history of every AI run)
$createSql = "CREATE TABLE IF NOT EXISTS imaging_ai_findings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    document_id   INT NOT NULL,
    patient_id    INT NOT NULL,
    run_by        INT NOT NULL COMMENT 'users.id',
    modality      VARCHAR(30) NOT NULL,
    findings_json JSON NOT NULL COMMENT 'Raw AI response: label, score, summary, explanation, recommendations',
    accepted      TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = provider accepted and saved to encounter',
    saved_to_note INT DEFAULT NULL COMMENT 'clinical_notes.id where finding was attached',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doc  (document_id),
    INDEX idx_pat  (patient_id),
    CONSTRAINT fk_iaf_document FOREIGN KEY (document_id) REFERENCES patient_documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_iaf_patient  FOREIGN KEY (patient_id)  REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    Database::query($createSql, []);
    echo "✅ imaging_ai_findings table created (or already exists).\n";
} catch (Exception $e) {
    echo "❌ imaging_ai_findings create: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
