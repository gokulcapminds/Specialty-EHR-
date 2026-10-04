<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\AuditLogger;

/**
 * C04 "Cardiac History" - one `patient_cardiac_profile` row per patient.
 *
 * This is patient-level, not encounter-level: the cardiac history is REVIEWED at each visit, never retyped. It holds the
 * inputs every cardiac risk score needs (ASCVD, CHA2DS2-VASc, HAS-BLED), so those can be computed instead of hand-entered.
 * Conditions that are also problem-list items (prior MI, AFib, ...) still belong in `patient_problems` with their ICD-10 -
 * this table is the risk-factor view of the same history, not a replacement for the problem list.
 *
 * Routed under /api/clinical/... so RouteAreas maps it to the `encounters` area (clinical roles only) automatically.
 */
class CardiacProfileController {
    /** Column => type. The single list that validation, the SELECT and the upsert all read, so the three can't drift apart. */
    private const FIELDS = [
        'hypertension' => 'bool', 'diabetes' => 'bool', 'dyslipidemia' => 'bool',
        'smoking_status' => 'enum:Never,Former,Current', 'pack_years' => 'decimal',
        'obesity' => 'bool', 'ckd' => 'bool', 'sleep_apnea' => 'bool', 'family_premature_cad' => 'bool',
        'cad' => 'bool',
        'prior_mi' => 'bool', 'prior_mi_date' => 'date',
        'prior_pci' => 'bool', 'prior_pci_date' => 'date',
        'prior_cabg' => 'bool', 'prior_cabg_date' => 'date',
        'heart_failure' => 'bool', 'hf_type' => 'enum:HFrEF,HFmrEF,HFpEF',
        'atrial_fibrillation' => 'bool', 'valve_disease' => 'bool', 'cardiomyopathy' => 'bool',
        'pad' => 'bool', 'stroke_tia' => 'bool',
        'device_type' => 'text60', 'device_implant_date' => 'date',
        'prior_bleeding' => 'bool', 'labile_inr' => 'bool', 'alcohol_excess' => 'bool',
        'notes' => 'text',
    ];

    private function patientId(array $params): int {
        return (int)($params['patient_id'] ?? $params['id'] ?? 0);
    }

    private function fail(int $code, string $message): void {
        http_response_code($code);
        echo json_encode(['status' => 'error', 'message' => $message]);
    }

    /** GET /api/clinical/cardiac-profile/{patient_id} - always returns a full profile, defaults when none is stored yet. */
    public function show(array $params): void {
        Roles::enforce(Roles::CLINICAL);
        header('Content-Type: application/json');
        $patientId = $this->patientId($params);
        if (!$patientId || !Database::fetch("SELECT id FROM patients WHERE id = ?", [$patientId])) {
            $this->fail(404, 'Patient not found.');
            return;
        }

        $row = Database::fetch("SELECT * FROM patient_cardiac_profile WHERE patient_id = ?", [$patientId]);
        $profile = ['patient_id' => $patientId, 'exists' => (bool)$row];
        foreach (self::FIELDS as $field => $type) {
            $raw = $row[$field] ?? null;
            $profile[$field] = $this->castOut($raw, $type);
        }
        $profile['last_reviewed_at'] = $row['last_reviewed_at'] ?? null;
        $profile['last_reviewed_by_name'] = null;
        if (!empty($row['last_reviewed_by'])) {
            $u = Database::fetch("SELECT first_name, last_name FROM users WHERE id = ?", [(int)$row['last_reviewed_by']]);
            if ($u) $profile['last_reviewed_by_name'] = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
        }

        AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null,
            $patientId, 'View Cardiac History', 'Clinical', (string)$patientId);

        echo json_encode(['status' => 'success', 'data' => $profile]);
    }

    /** PUT /api/clinical/cardiac-profile/{patient_id} - upsert; marks the history as reviewed now, by this user. */
    public function update(array $params): void {
        Roles::enforce(Roles::CLINICAL);
        header('Content-Type: application/json');
        $patientId = $this->patientId($params);
        if (!$patientId || !Database::fetch("SELECT id FROM patients WHERE id = ?", [$patientId])) {
            $this->fail(404, 'Patient not found.');
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $this->fail(400, 'Invalid request.');
            return;
        }

        // Build column list and values from one source, so placeholders can never drift out of step (Rule 1).
        $columns = [];
        $values  = [];
        foreach (self::FIELDS as $field => $type) {
            if (!array_key_exists($field, $input)) continue;
            [$value, $error] = $this->castIn($input[$field], $type, $field);
            if ($error) {
                $this->fail(400, $error);
                return;
            }
            $columns[] = $field;
            $values[]  = $value;
        }
        if (!$columns) {
            $this->fail(400, 'No cardiac history fields were sent.');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $columns[] = 'last_reviewed_at'; $values[] = date('Y-m-d H:i:s');
        $columns[] = 'last_reviewed_by'; $values[] = $userId;

        $insertCols = array_merge(['patient_id'], $columns);
        $placeholders = implode(', ', array_fill(0, count($insertCols), '?'));
        $updateSet = implode(', ', array_map(fn($c) => "`$c` = VALUES(`$c`)", $columns));
        $sql = "INSERT INTO patient_cardiac_profile (" . implode(', ', array_map(fn($c) => "`$c`", $insertCols)) . ")
                VALUES ($placeholders) ON DUPLICATE KEY UPDATE $updateSet";

        try {
            Database::query($sql, array_merge([$patientId], $values));
        } catch (\Throwable $e) {
            error_log('CardiacProfileController::update DB error: ' . $e->getMessage());
            $this->fail(500, 'Could not save the cardiac history.');
            return;
        }

        AuditLogger::log($userId, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null,
            $patientId, 'Update Cardiac History', 'Clinical', (string)$patientId);

        echo json_encode(['status' => 'success', 'message' => 'Cardiac history saved.']);
    }

    /** Stored value -> API value. */
    private function castOut($raw, string $type) {
        if (strpos($type, 'enum:') === 0) return $raw !== null && $raw !== '' ? $raw : ($type === 'enum:Never,Former,Current' ? 'Never' : null);
        switch ($type) {
            case 'bool':    return (int)$raw === 1;
            case 'decimal': return $raw === null || $raw === '' ? null : (float)$raw;
            case 'date':    return $raw ?: null;
            default:        return $raw ?? '';
        }
    }

    /** API value -> stored value. Returns [value, errorMessage|null]. */
    private function castIn($raw, string $type, string $field): array {
        if (strpos($type, 'enum:') === 0) {
            $allowed = explode(',', substr($type, 5));
            $v = is_string($raw) ? trim($raw) : '';
            if ($v === '') return [$field === 'smoking_status' ? 'Never' : null, null];
            if (!in_array($v, $allowed, true)) {
                return [null, ucfirst(str_replace('_', ' ', $field)) . ' must be one of: ' . implode(', ', $allowed) . '.'];
            }
            return [$v, null];
        }
        switch ($type) {
            case 'bool':
                return [!empty($raw) && $raw !== '0' && $raw !== 'false' ? 1 : 0, null];
            case 'decimal':
                if ($raw === null || $raw === '') return [null, null];
                if (!is_numeric($raw) || (float)$raw < 0 || (float)$raw > 999) {
                    return [null, 'Pack-years must be a number between 0 and 999.'];
                }
                return [round((float)$raw, 1), null];
            case 'date':
                if ($raw === null || $raw === '') return [null, null];
                $d = \DateTime::createFromFormat('Y-m-d', (string)$raw);
                if (!$d || $d->format('Y-m-d') !== (string)$raw) {
                    return [null, ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date (YYYY-MM-DD).'];
                }
                if ($d > new \DateTime('today')) {
                    return [null, ucfirst(str_replace('_', ' ', $field)) . ' cannot be in the future.'];
                }
                return [$raw, null];
            case 'text60':
                return [mb_substr(trim((string)$raw), 0, 60), null];
            default:
                return [mb_substr(trim((string)$raw), 0, 5000), null];
        }
    }
}
