<?php
namespace App\Services;

use App\Models\Database;

/**
 * Persistence for EDI transactions (270/271/837/999/277CA/835) and eligibility checks.
 * The X12 text contains PHI (names, DOB, address, member ID), so it is stored encrypted like other PII
 * (EncryptionService, random IV) and is only ever returned to an authenticated caller.
 */
class EdiStore {
    public static function store(?int $claimId, int $patientId, string $type, string $direction, string $status, string $content, array $summary = [], ?string $control = null): int {
        Database::query(
            "INSERT INTO edi_transactions (claim_id, patient_id, type, direction, control_number, status, content_encrypted, summary_json, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$claimId, $patientId, $type, $direction, $control, $status, EncryptionService::encrypt($content), $summary ? json_encode($summary) : null, $_SESSION['user_id'] ?? null]
        );
        return (int)Database::lastInsertId();
    }

    public static function get(int $txId): ?array {
        $r = Database::fetch("SELECT id, claim_id, patient_id, type, direction, control_number, status, summary_json, created_at FROM edi_transactions WHERE id = ?", [$txId]);
        if (!$r) return null;
        $r['summary'] = $r['summary_json'] ? (json_decode($r['summary_json'], true) ?: []) : [];
        unset($r['summary_json']);
        return $r;
    }

    public static function content(int $txId): ?string {
        $r = Database::fetch("SELECT content_encrypted FROM edi_transactions WHERE id = ?", [$txId]);
        if (!$r || $r['content_encrypted'] === null || $r['content_encrypted'] === '') return null;
        try { return (string)EncryptionService::decrypt($r['content_encrypted']); } catch (\Throwable $e) { return null; }
    }

    /** Updates status and merges keys into the summary (e.g. the clearinghouse ref and scenario of a sent 837). */
    public static function update(int $txId, string $status, array $summaryMerge = []): void {
        $row = self::get($txId);
        $summary = array_merge($row['summary'] ?? [], $summaryMerge);
        Database::query("UPDATE edi_transactions SET status = ?, summary_json = ? WHERE id = ?", [$status, $summary ? json_encode($summary) : null, $txId]);
    }

    public static function setStatus(int $txId, string $status): void {
        Database::query("UPDATE edi_transactions SET status = ? WHERE id = ?", [$status, $txId]);
    }

    public static function listForClaim(int $claimId): array {
        $rows = Database::fetchAll("SELECT id, type, direction, control_number, status, summary_json, created_at FROM edi_transactions WHERE claim_id = ? ORDER BY id DESC", [$claimId]);
        foreach ($rows as &$r) {
            $r['summary'] = $r['summary_json'] ? (json_decode($r['summary_json'], true) ?: []) : [];
            unset($r['summary_json']);
        }
        unset($r);
        return $rows;
    }

    public static function latestEligibility(int $patientId, string $sequence): ?array {
        $r = Database::fetch("SELECT * FROM eligibility_checks WHERE patient_id = ? AND sequence = ? ORDER BY id DESC LIMIT 1", [$patientId, $sequence]);
        return $r ?: null;
    }

    public static function mode(): string {
        $r = Database::fetch("SELECT setting_value v FROM system_settings WHERE setting_key = 'clearinghouse_mode'");
        return trim((string)($r['v'] ?? '')) ?: 'simulator';
    }

    /** The received-but-not-yet-posted 835, described against the claim's lines (or null). */
    public static function pending835(array $claim): ?array {
        $txId = (int)($claim['pending_835_tx_id'] ?? 0);
        if (!$txId) return null;
        $tx = self::get($txId);
        if (!$tx || $tx['status'] !== 'received') return null;
        $content = self::content($txId);
        if ($content === null) return null;
        $lines = Database::fetchAll("SELECT * FROM claim_lines WHERE claim_id = ? ORDER BY id", [$claim['id']]);
        $d = Claim835Parser::describeForClaim(Claim835Parser::parse($content), $lines, (string)$claim['claim_number']);
        $d['transaction_id'] = $txId;
        $d['received_at'] = $tx['created_at'];
        return $d;
    }

    /** The `edi` block returned with a claim's detail. */
    public static function summary(array $claim): array {
        $validation = !empty($claim['validation_json']) ? json_decode($claim['validation_json'], true) : null;
        $elig = self::latestEligibility((int)$claim['patient_id'], $claim['sequence']);
        $eligCurrent = false;
        if ($elig) {
            $eligCurrent = strtotime($elig['checked_at']) >= strtotime('-7 days');
        }
        return [
            'mode'           => self::mode(),
            'eligibility'    => $elig,
            'eligibility_current' => $eligCurrent,
            'transactions'   => self::listForClaim((int)$claim['id']),
            'validation'     => $validation,
            'validated_at'   => $claim['validated_at'] ?? null,
            'clearinghouse_ref' => $claim['clearinghouse_ref'] ?? null,
            'pending_835_tx_id' => $claim['pending_835_tx_id'] ?? null,
            'pending_835'    => self::pending835($claim),
        ];
    }
}
