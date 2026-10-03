<?php
namespace App\Services;

/**
 * Small pure parsers for the other inbound X12 documents: the 271 eligibility response and the
 * 999 / 277CA acknowledgments. Used the same way for the simulator and for a real clearinghouse.
 */
class EdiResponseParsers {
    private static function segments(string $x12): array {
        $segs = [];
        foreach (preg_split('/~/', $x12) as $r) {
            $r = trim($r, "\r\n ");
            if ($r !== '') $segs[] = explode('*', $r);
        }
        return $segs;
    }

    /** 271 -> ['status' => Active|Inactive|Error, 'plan', 'copay', 'deductible', 'deductible_met', 'coinsurance_pct', 'message'] */
    public static function parse271(string $x12): array {
        $r = ['status' => 'Error', 'plan' => '', 'copay' => null, 'deductible' => null, 'deductible_remaining' => null, 'deductible_met' => null, 'coinsurance_pct' => null, 'message' => ''];
        foreach (self::segments($x12) as $s) {
            $tag = $s[0];
            if ($tag === 'AAA') {
                $r['status'] = 'Error';
                $r['message'] = 'The payer could not process the request (AAA ' . ($s[3] ?? '') . ').';
            } elseif ($tag === 'EB') {
                $code = $s[1] ?? '';
                if ($code === '1') { $r['status'] = 'Active'; if (($s[5] ?? '') !== '') $r['plan'] = $s[5]; }
                elseif ($code === '6') { $r['status'] = 'Inactive'; if (($s[5] ?? '') !== '') $r['plan'] = $s[5]; }
                elseif ($code === 'B' && ($s[7] ?? '') !== '') $r['copay'] = (float)$s[7];
                elseif ($code === 'C') {
                    if (($s[6] ?? '') === '23') $r['deductible'] = (float)($s[7] ?? 0);
                    if (($s[6] ?? '') === '29') $r['deductible_remaining'] = (float)($s[7] ?? 0);
                }
                elseif ($code === 'A' && ($s[8] ?? '') !== '') $r['coinsurance_pct'] = round((float)$s[8] * 100, 2);
            } elseif ($tag === 'MSG') {
                $r['message'] = $s[1] ?? '';
            }
        }
        if ($r['deductible'] !== null && $r['deductible_remaining'] !== null) $r['deductible_met'] = round($r['deductible'] - $r['deductible_remaining'], 2);
        return $r;
    }

    /** 999 -> ['accepted' => bool, 'errors' => [string, ...]] */
    public static function parse999(string $x12): array {
        $accepted = false; $errors = [];
        foreach (self::segments($x12) as $s) {
            if ($s[0] === 'AK9') $accepted = ($s[1] ?? '') === 'A';
            if ($s[0] === 'IK3') $errors[] = 'Segment ' . ($s[1] ?? '?') . ' at position ' . ($s[2] ?? '?') . ' (syntax error code ' . ($s[4] ?? '?') . ')';
            if ($s[0] === 'NTE') $errors[] = $s[2] ?? '';
        }
        return ['accepted' => $accepted, 'errors' => array_values(array_filter($errors))];
    }

    /** 277CA -> ['accepted' => bool, 'category' => 'A2', 'payer_claim_no' => '', 'message' => ''] */
    public static function parse277(string $x12): array {
        $r = ['accepted' => false, 'category' => '', 'payer_claim_no' => '', 'message' => ''];
        foreach (self::segments($x12) as $s) {
            if ($s[0] === 'STC') {
                $cat = explode(':', $s[1] ?? '');
                $r['category'] = $cat[0] ?? '';
                $r['accepted'] = ($cat[0] ?? '') === 'A2';
                $r['message'] = $s[12] ?? '';
            }
            if ($s[0] === 'REF' && ($s[1] ?? '') === '1K') $r['payer_claim_no'] = $s[2] ?? '';
        }
        return $r;
    }
}
