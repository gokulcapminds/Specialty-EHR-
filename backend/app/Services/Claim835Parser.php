<?php
namespace App\Services;

/**
 * Parses an ANSI X12 835 (electronic remittance advice) and maps it onto our claim lines.
 * Pure function of the text, so it works the same for the simulator and for a real clearinghouse feed.
 */
class Claim835Parser {
    /** Common claim adjustment reason codes (CARC); anything else is shown as "Code n". */
    public const CARC = [
        '1'   => 'Deductible amount',
        '2'   => 'Coinsurance amount',
        '3'   => 'Co-payment amount',
        '16'  => 'Claim lacks information needed for adjudication',
        '18'  => 'Duplicate claim/service',
        '27'  => 'Expenses incurred after coverage terminated',
        '29'  => 'The time limit for filing has expired',
        '45'  => 'Charge exceeds fee schedule / maximum allowable (contractual adjustment)',
        '50'  => 'Not deemed a medical necessity by the payer',
        '96'  => 'Non-covered charge(s)',
        '97'  => 'Benefit for this service is included in another service (bundled)',
        '197' => 'Precertification / authorization absent',
    ];
    /** Reasons that are patient cost-sharing or contractual write-downs, i.e. NOT a denial. */
    private const NOT_DENIAL = ['1', '2', '3', '45'];

    public static function describe(string $carc): string {
        return self::CARC[$carc] ?? "Adjustment reason {$carc}";
    }

    public static function parse(string $x12): array {
        $segs = [];
        foreach (preg_split('/~/', $x12) as $r) {
            $r = trim($r, "\r\n ");
            if ($r !== '') $segs[] = explode('*', $r);
        }
        $out = ['trace' => '', 'payer' => '', 'payer_id' => '', 'payment_total' => 0.0, 'payment_method' => '', 'payment_date' => '', 'claims' => []];
        $ci = -1; $li = -1;
        foreach ($segs as $s) {
            $tag = $s[0];
            if ($tag === 'BPR') {
                $out['payment_total'] = (float)($s[2] ?? 0);
                $out['payment_method'] = $s[4] ?? '';
                $out['payment_date'] = $s[16] ?? '';
            } elseif ($tag === 'TRN') {
                $out['trace'] = $s[2] ?? '';
            } elseif ($tag === 'N1' && ($s[1] ?? '') === 'PR') {
                $out['payer'] = $s[2] ?? '';
                $out['payer_id'] = $s[4] ?? '';
            } elseif ($tag === 'CLP') {
                $ci++; $li = -1;
                $statusCode = $s[2] ?? '';
                $out['claims'][$ci] = [
                    'claim_number'   => $s[1] ?? '',
                    'status_code'    => $statusCode,
                    'status'         => ['1' => 'Processed as primary', '2' => 'Processed as secondary', '3' => 'Processed as tertiary', '4' => 'Denied', '22' => 'Reversal'][$statusCode] ?? "Status {$statusCode}",
                    'charge'         => (float)($s[3] ?? 0),
                    'paid'           => (float)($s[4] ?? 0),
                    'patient_resp'   => (float)($s[5] ?? 0),
                    'payer_claim_no' => $s[7] ?? '',
                    'lines'          => [],
                    'adjustments'    => [],
                ];
            } elseif ($ci >= 0 && $tag === 'SVC') {
                $proc = explode(':', $s[1] ?? '');
                $li++;
                $out['claims'][$ci]['lines'][$li] = [
                    'cpt'         => $proc[1] ?? '',
                    'modifiers'   => implode(',', array_slice($proc, 2)),
                    'charge'      => (float)($s[2] ?? 0),
                    'paid'        => (float)($s[3] ?? 0),
                    'units'       => (int)($s[5] ?? 1),
                    'allowed'     => null,
                    'adjustments' => [],
                ];
            } elseif ($ci >= 0 && $tag === 'AMT' && ($s[1] ?? '') === 'B6' && $li >= 0) {
                $out['claims'][$ci]['lines'][$li]['allowed'] = (float)($s[2] ?? 0);
            } elseif ($ci >= 0 && $tag === 'CAS') {
                $group = $s[1] ?? '';
                for ($i = 2; $i + 1 < count($s); $i += 3) {
                    if (($s[$i] ?? '') === '') continue;
                    $adj = ['group' => $group, 'carc' => $s[$i], 'amount' => (float)($s[$i + 1] ?? 0), 'description' => self::describe($s[$i])];
                    if ($li >= 0) $out['claims'][$ci]['lines'][$li]['adjustments'][] = $adj;
                    else $out['claims'][$ci]['adjustments'][] = $adj;
                }
            }
        }
        // derive line-level allowed when the payer did not send AMT*B6: charge minus contractual (CO-45) write-down
        foreach ($out['claims'] as &$c) {
            foreach ($c['lines'] as &$l) {
                if ($l['allowed'] === null) {
                    $contractual = 0.0;
                    foreach ($l['adjustments'] as $a) { if ($a['group'] === 'CO' && $a['carc'] === '45') $contractual += $a['amount']; }
                    $l['allowed'] = round($l['charge'] - $contractual, 2);
                }
                $l['denied'] = false; $l['denial_code'] = ''; $l['denial_reason'] = '';
                if ($l['paid'] <= 0.0049) {
                    foreach ($l['adjustments'] as $a) {
                        if (!in_array($a['carc'], self::NOT_DENIAL, true) && $a['group'] !== 'PR') {
                            $l['denied'] = true; $l['denial_code'] = $a['group'] . '-' . $a['carc']; $l['denial_reason'] = $a['description'];
                            break;
                        }
                    }
                }
            }
            unset($l);
        }
        unset($c);
        $out['claims'] = array_values(array_map(function ($c) { $c['lines'] = array_values($c['lines']); return $c; }, $out['claims']));
        return $out;
    }

    /**
     * A display-ready summary of one claim inside a parsed 835: per-line numbers, totals, the outcome our app will
     * record (Paid / Partially Paid / Denied) and any reconciliation problems that should block posting.
     */
    public static function describeForClaim(array $parsed, array $claimLines, string $claimNumber): array {
        $pc = null;
        foreach ($parsed['claims'] as $c) { if (strcasecmp($c['claim_number'], $claimNumber) === 0) { $pc = $c; break; } }
        if ($pc === null) return ['found' => false, 'problems' => ['This remittance does not contain claim ' . $claimNumber . '.'], 'lines' => [], 'totals' => [], 'outcome' => null];
        $map = self::mapToClaimLines($pc, $claimLines);
        $lines = []; $t = ['charge' => 0.0, 'allowed' => 0.0, 'paid' => 0.0, 'contractual' => 0.0, 'patient_resp' => 0.0];
        $denied = 0;
        foreach ($pc['lines'] as $l) {
            $co = 0.0; $pr = 0.0;
            foreach ($l['adjustments'] as $a) { if ($a['group'] === 'CO' && $a['carc'] === '45') $co += $a['amount']; if ($a['group'] === 'PR') $pr += $a['amount']; }
            $lines[] = ['cpt' => $l['cpt'], 'modifiers' => $l['modifiers'], 'charge' => $l['charge'], 'allowed' => $l['denied'] ? 0.0 : $l['allowed'], 'paid' => $l['paid'], 'contractual' => round($co, 2), 'patient_resp' => round($pr, 2),
                'denied' => $l['denied'], 'denial_code' => $l['denial_code'], 'denial_reason' => $l['denial_reason'], 'adjustments' => $l['adjustments']];
            $t['charge'] += $l['charge']; $t['allowed'] += $l['denied'] ? 0 : $l['allowed']; $t['paid'] += $l['paid']; $t['contractual'] += $co; $t['patient_resp'] += $pr;
            if ($l['denied']) $denied++;
        }
        foreach ($t as $k => $v) $t[$k] = round($v, 2);
        $problems = [];
        if (!$map['ok']) $problems[] = $map['message'];
        if (abs($parsed['payment_total'] - $t['paid']) > 0.005) $problems[] = 'The payment total (' . number_format($parsed['payment_total'], 2) . ') does not match the sum of the paid lines (' . number_format($t['paid'], 2) . ').';
        if (abs($pc['paid'] - $t['paid']) > 0.005) $problems[] = 'The claim-level paid amount does not match the sum of the paid lines.';
        $outcome = !count($lines) ? null : ($denied === count($lines) ? 'Denied' : ($denied > 0 ? 'Partially Paid' : 'Paid'));
        $denialReason = '';
        foreach ($lines as $l) { if ($l['denied']) { $denialReason = $l['denial_reason']; break; } }
        return [
            'found' => true, 'trace' => $parsed['trace'], 'payer' => $parsed['payer'], 'payment_total' => $parsed['payment_total'], 'payment_date' => $parsed['payment_date'],
            'payer_claim_no' => $pc['payer_claim_no'], 'claim_status' => $pc['status'], 'lines' => $lines, 'totals' => $t, 'outcome' => $outcome,
            'denial_reason' => $denialReason, 'problems' => $problems, 'postable' => count($problems) === 0,
        ];
    }

    /**
     * Maps a parsed 835 claim onto our claim_lines (matched by CPT in order) for ClaimController::applyRemit.
     * Returns ['ok' => bool, 'message' => string, 'lines' => [...], 'denial_reason' => string].
     */
    public static function mapToClaimLines(array $parsedClaim, array $claimLines): array {
        $used = [];
        $lines = [];
        $denialReason = '';
        foreach ($claimLines as $cl) {
            $match = null;
            foreach ($parsedClaim['lines'] as $i => $pl) {
                if (isset($used[$i])) continue;
                if (strcasecmp(trim($pl['cpt']), trim((string)$cl['cpt_code'])) === 0) { $match = $i; break; }
            }
            if ($match === null) return ['ok' => false, 'message' => "The remittance has no line for procedure {$cl['cpt_code']}.", 'lines' => [], 'denial_reason' => ''];
            $used[$match] = true;
            $pl = $parsedClaim['lines'][$match];
            $lines[] = [
                'id'          => (int)$cl['id'],
                'allowed'     => $pl['denied'] ? 0 : $pl['allowed'],
                'paid'        => $pl['denied'] ? 0 : $pl['paid'],
                'denial_code' => $pl['denied'] ? $pl['denial_code'] : '',
            ];
            if ($pl['denied'] && $denialReason === '') $denialReason = $pl['denial_reason'];
        }
        return ['ok' => true, 'message' => '', 'lines' => $lines, 'denial_reason' => $denialReason];
    }
}
