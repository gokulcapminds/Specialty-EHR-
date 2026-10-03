<?php
namespace App\Services\Clearinghouse;

use App\Services\Claim837Validator;
use App\Services\EdiResponseParsers;

/**
 * A stand-in for the clearinghouse AND the payer, used until a live Availity connection exists.
 * It speaks real X12 (270/271, 999, 277CA, 835) but its adjudication rules are ILLUSTRATIVE, not any real payer's logic.
 *
 * Scenarios (chosen when sending / checking eligibility):
 *   auto     - rule based: 80% allowed, remaining deductible first (capped at half the allowed total), then copay, then 20% coinsurance
 *   paid     - clean payment: 80% allowed, 20% coinsurance only
 *   partial  - the last line is denied (CO-50) and the rest pay (80% allowed, 20% coinsurance); a single-line claim is allowed at 50% instead
 *   denied   - every line denied (CO-50, or CO-27 when coverage is inactive)
 *   rejected - refused at the acknowledgment stage (277CA), no remittance
 *   inactive - (eligibility only) coverage comes back Inactive
 */
class SimulatorClearinghouse implements ClearinghouseInterface {
    public function name(): string { return 'Simulator'; }

    // ------------------------------------------------------------------ X12 helpers
    private static function clean($v): string {
        return strtoupper(trim(preg_replace('/[*~:^|\\\\]+/', ' ', (string)$v)));
    }
    private static function digits($v): string { return preg_replace('/\D/', '', (string)$v); }
    private static function amt(float $v): string {
        $s = number_format($v, 2, '.', '');
        $t = rtrim(rtrim($s, '0'), '.');
        return $t === '' ? '0' : $t;
    }
    private static function d8($date): string { return $date ? date('Ymd', strtotime((string)$date)) : date('Ymd'); }

    /** Wraps body segments in ISA/GS/ST...SE/GE/IEA. Inbound documents are sent by the payer to our submitter ID. */
    private function envelope(string $set, string $funcId, string $version, array $body, int $ctl, string $sender, string $receiver): string {
        $st = array_merge(["ST*{$set}*0001*{$version}"], $body);
        $st[] = 'SE*' . (count($st) + 1) . '*0001';
        $c9 = str_pad((string)($ctl % 1000000000), 9, '0', STR_PAD_LEFT);
        $isa = 'ISA*00*' . str_repeat(' ', 10) . '*00*' . str_repeat(' ', 10) . '*ZZ*' . str_pad(substr(self::clean($sender), 0, 15), 15) . '*ZZ*' . str_pad(substr(self::clean($receiver), 0, 15), 15)
            . '*' . date('ymd') . '*' . date('Hi') . '*^*00501*' . $c9 . '*0*T*:';
        $gs = "GS*{$funcId}*" . self::clean($sender) . '*' . self::clean($receiver) . '*' . date('Ymd') . '*' . date('Hi') . '*' . ($ctl % 1000000000) . '*X*' . $version;
        return implode("~\n", array_merge([$isa, $gs], $st, ['GE*1*' . ($ctl % 1000000000), 'IEA*1*' . $c9])) . "~\n";
    }

    private function ctl(): int { return (int)(microtime(true) * 10) % 900000000 + 100000000; }

    // ------------------------------------------------------------------ 270 / 271
    public function checkEligibility(array $req): array {
        $pt = $req['patient']; $cov = $req['coverage']; $payer = $req['payer']; $fac = $req['facility']; $set = $req['settings'];
        $scenario = $req['scenario'] ?? 'auto';
        $sender = $set['billing_submitter_id'] ?: 'CLINIC';
        $payerId = $payer['payer_id'] ?: 'PAYER';
        $ctl = $this->ctl();
        $trace = 'ELG' . date('ymdHis');
        $now = date('Ymd'); $hm = date('Hi');
        $member = self::clean($cov['member_id'] ?? '');
        $subject = [
            'NM1*IL*1*' . self::clean($pt['last']) . '*' . self::clean($pt['first']) . '****MI*' . $member,
            'DMG*D8*' . self::d8($pt['dob']),
        ];
        $head = fn(string $bhtPurpose) => [
            "BHT*0022*{$bhtPurpose}*{$ctl}*{$now}*{$hm}",
            'HL*1**20*1',
            'NM1*PR*2*' . self::clean($payer['name']) . '*****PI*' . self::clean($payerId),
            'HL*2*1*21*1',
            'NM1*1P*2*' . self::clean($fac['name']) . '*****XX*' . self::digits($fac['npi']),
            'HL*3*2*22*0',
        ];
        $reqBody = array_merge($head('13'), ["TRN*1*{$trace}*" . self::clean($sender)], $subject, ["DTP*291*D8*{$now}", 'EQ*30']);
        $request = $this->envelope('270', 'HS', '005010X279A1', $reqBody, $ctl, $sender, $payerId);

        // payer-side decision (illustrative)
        $inactive = ($scenario === 'inactive') || (!empty($cov['effective_date']) && $cov['effective_date'] > date('Y-m-d'));
        $plan = self::clean($cov['plan_name'] ?: 'HEALTH PLAN');
        $copay = (isset($cov['copay']) && (float)$cov['copay'] > 0) ? (float)$cov['copay'] : 30.0;
        $deductible = 1000.0;
        $met = (crc32($member) % 8) * 125.0;
        $resBody = array_merge($head('11'), ["TRN*2*{$trace}*" . self::clean($payerId)], $subject);
        if ($inactive) {
            $resBody[] = 'EB*6**30**' . $plan;
            $resBody[] = 'MSG*Coverage is not active for the date of service (simulated response)';
        } else {
            $resBody[] = 'EB*1**30**' . $plan;
            $resBody[] = 'EB*B*IND*98**' . $plan . '**' . self::amt($copay);
            $resBody[] = 'EB*C*IND*30**' . $plan . '*23*' . self::amt($deductible);
            $resBody[] = 'EB*C*IND*30**' . $plan . '*29*' . self::amt($deductible - $met);
            $resBody[] = 'EB*A*IND*30**' . $plan . '***0.2';
            $resBody[] = 'MSG*Coverage is active (simulated response)';
        }
        $response = $this->envelope('271', 'HB', '005010X279A1', $resBody, $ctl + 1, $payerId, $sender);
        return ['request_x12' => $request, 'response_x12' => $response];
    }

    // ------------------------------------------------------------------ 837 intake -> 999 + 277CA
    public function submitClaim(string $x12, array $ctx): array {
        $claim = $ctx['claim']; $set = $ctx['settings']; $payer = $ctx['payer']; $scenario = $ctx['scenario'] ?? 'auto';
        $sender = $set['billing_submitter_id'] ?: 'CLINIC';
        $payerId = $claim['payer_id_code'] ?: 'PAYER';
        $ref = 'SIM' . date('ymdHis') . str_pad((string)$claim['id'], 4, '0', STR_PAD_LEFT);
        $ctl = $this->ctl();

        // 999: syntax level. Re-validate what actually arrived; a structurally bad file is rejected here.
        $v = Claim837Validator::validate($x12);
        $structural = array_values(array_filter($v['issues'], fn($i) => $i['level'] === 'error' && in_array($i['code'], ['ENV_ISA', 'ISA_LENGTH', 'ENV_ORDER', 'ISA_CONTROL', 'GS_CONTROL', 'ST_CONTROL', 'SE_COUNT', 'ENV_COUNTS'], true)));
        $ak = ['AK1*HC*' . ($ctl % 1000000000) . '*005010X222A1', 'AK2*837*0001*005010X222A1'];
        if ($structural) {
            $ik3 = [];
            foreach ($structural as $i) { $ik3[] = 'IK3*' . self::clean(explode(' ', $i['segment'])[0]) . '*1**8'; }
            $body = array_merge($ak, $ik3, ['IK5*R*5', 'AK9*R*1*1*0']);
            return ['ref' => $ref, 'ack_999' => $this->envelope('999', 'FA', '005010X231A1', $body, $ctl, $payerId, $sender), 'ack_277ca' => null];
        }
        $ack999 = $this->envelope('999', 'FA', '005010X231A1', array_merge($ak, ['IK5*A', 'AK9*A*1*1*1']), $ctl, $payerId, $sender);

        // 277CA: business level acknowledgment from the payer
        $pcn = 'SIM-' . str_pad((string)$claim['id'], 6, '0', STR_PAD_LEFT);
        $patient = $ctx['patient']; $cov = $ctx['coverage']; $fac = $ctx['facility'];
        $rejected = ($scenario === 'rejected');
        $stc = $rejected
            ? 'STC*A3:21:PR*' . date('Ymd') . '*U*' . self::amt((float)$claim['billed_amount']) . '*0*******Member ID was not found for this payer (simulated rejection)'
            : 'STC*A2:20:PR*' . date('Ymd') . '*WQ*' . self::amt((float)$claim['billed_amount']);
        $body = [
            'BHT*0085*08*' . $ref . '*' . date('Ymd') . '*' . date('Hi') . '*TH',
            'HL*1**20*1',
            'NM1*PR*2*' . self::clean($payer['name'] ?? $claim['payer_name']) . '*****PI*' . self::clean($payerId),
            'HL*2*1*21*1',
            'NM1*41*2*' . self::clean($fac['legal_entity_name'] ?: $fac['facility_name']) . '*****46*' . self::clean($sender),
            'HL*3*2*19*1',
            'NM1*85*2*' . self::clean($fac['legal_entity_name'] ?: $fac['facility_name']) . '*****XX*' . self::digits($fac['npi']),
            'HL*4*3*22*0',
            'NM1*QC*1*' . self::clean($patient['last']) . '*' . self::clean($patient['first']) . '****MI*' . self::clean($cov['member_id'] ?? ''),
            'TRN*2*' . self::clean($claim['claim_number']),
            $stc,
        ];
        if (!$rejected) $body[] = 'REF*1K*' . $pcn;
        $body[] = 'DTP*472*RD8*' . self::d8($claim['date_of_service']);
        $ack277 = $this->envelope('277', 'HN', '005010X214', $body, $ctl + 1, $payerId, $sender);
        return ['ref' => $ref, 'ack_999' => $ack999, 'ack_277ca' => $ack277];
    }

    // ------------------------------------------------------------------ 835
    /** Per-line adjudication. Returns [ ['cpt','mods','charge','units','allowed','paid','adj' => [[group, carc, amount], ...], 'denied' => bool], ...] */
    private function adjudicate(array $ctx): array {
        $claim = $ctx['claim']; $lines = $ctx['lines']; $scenario = $ctx['scenario'] ?? 'auto';
        $isSecondary = $claim['sequence'] === 'Secondary';
        $inactive = ($ctx['eligibility_status'] ?? 'Active') === 'Inactive';
        $cov = $ctx['coverage'];
        $dedRemaining = 1000.0 - ((crc32(self::clean($cov['member_id'] ?? '')) % 8) * 125.0);
        $copay = (isset($cov['copay']) && (float)$cov['copay'] > 0) ? (float)$cov['copay'] : 30.0;
        $copayLeft = $copay;
        // Illustrative guard: the deductible never swallows more than half of the allowed total, so 'auto' always shows a real payment
        $dedRemaining = min($dedRemaining, round(0.5 * 0.8 * array_sum(array_map(fn($l) => (float)$l['charge'], $lines)), 2));
        $useCostShare = ($scenario === 'auto') && !$isSecondary;   // only 'auto' applies deductible/copay; 'paid' and 'partial' pay the covered line with 20% coinsurance
        $n = count($lines);
        $out = [];
        foreach ($lines as $i => $l) {
            $charge = round((float)$l['charge'], 2);
            $row = ['cpt' => $l['cpt_code'], 'mods' => $l['modifiers'] ?? '', 'charge' => $charge, 'units' => (int)$l['units'], 'allowed' => 0.0, 'paid' => 0.0, 'adj' => [], 'denied' => false];
            $deny = ($scenario === 'denied') || $inactive || ($scenario === 'partial' && $n > 1 && $i === $n - 1);
            if ($deny) {
                $row['denied'] = true;
                $row['adj'][] = ['CO', $inactive ? '27' : '50', $charge];
                $out[] = $row; continue;
            }
            $rate = ($isSecondary) ? 1.0 : (($scenario === 'partial' && $n === 1) ? 0.5 : 0.8);
            $allowed = round($charge * $rate, 2);
            $contractual = round($charge - $allowed, 2);
            $ded = 0.0; $cp = 0.0;
            if ($useCostShare) {
                $ded = round(min($dedRemaining, $allowed), 2); $dedRemaining -= $ded;
                $afterDed = $allowed - $ded;
                if ($copayLeft > 0 && $afterDed > 0) { $cp = round(min($copayLeft, $afterDed), 2); $copayLeft -= $cp; }
            }
            $coins = round(($allowed - $ded - $cp) * 0.2, 2);
            $resp = round($ded + $cp + $coins, 2);
            $row['allowed'] = $allowed;
            $row['paid'] = round($allowed - $resp, 2);
            if ($contractual > 0) $row['adj'][] = ['CO', '45', $contractual];
            if ($ded > 0) $row['adj'][] = ['PR', '1', $ded];
            if ($cp > 0) $row['adj'][] = ['PR', '3', $cp];
            if ($coins > 0) $row['adj'][] = ['PR', '2', $coins];
            $out[] = $row;
        }
        return $out;
    }

    public function fetchRemittance(string $ref, array $ctx): array {
        $claim = $ctx['claim']; $set = $ctx['settings']; $payer = $ctx['payer']; $fac = $ctx['facility']; $patient = $ctx['patient']; $cov = $ctx['coverage'];
        if (($ctx['scenario'] ?? 'auto') === 'rejected') return ['ready' => false, 'message' => 'The claim was rejected at acknowledgment, so there is no remittance to receive.', 'x12' => null];
        $sender = $set['billing_submitter_id'] ?: 'CLINIC';
        $payerId = $claim['payer_id_code'] ?: 'PAYER';
        $rows = $this->adjudicate($ctx);
        $charge = 0.0; $paid = 0.0; $resp = 0.0; $deniedCount = 0;
        foreach ($rows as $r) {
            $charge += $r['charge']; $paid += $r['paid'];
            foreach ($r['adj'] as [$g, $c, $a]) { if ($g === 'PR') $resp += $a; }
            if ($r['denied']) $deniedCount++;
        }
        $charge = round($charge, 2); $paid = round($paid, 2); $resp = round($resp, 2);
        $allDenied = $deniedCount === count($rows);
        $trace = 'TRC' . date('ymd') . str_pad((string)$claim['id'], 6, '0', STR_PAD_LEFT);
        $pcn = 'SIM-' . str_pad((string)$claim['id'], 6, '0', STR_PAD_LEFT);
        $filing = $payer['claim_filing_code'] ?? 'CI';
        $body = [
            $paid > 0
                ? 'BPR*I*' . self::amt($paid) . '*C*ACH*CCP*01*000000000*DA*0000000000*1000000000**01*000000000*DA*0000000000*' . date('Ymd')
                : 'BPR*H*0*C*NON',
            'TRN*1*' . $trace . '*1' . str_pad(self::digits($payerId) ?: '0', 9, '0', STR_PAD_LEFT),
            'DTM*405*' . date('Ymd'),
            'N1*PR*' . self::clean($payer['name'] ?? $claim['payer_name']) . '*XV*' . self::clean($payerId),
            'N1*PE*' . self::clean($fac['legal_entity_name'] ?: $fac['facility_name']) . '*XX*' . self::digits($fac['npi']),
            'LX*1',
            'CLP*' . self::clean($claim['claim_number']) . '*' . ($allDenied ? '4' : ($claim['sequence'] === 'Secondary' ? '2' : '1')) . '*' . self::amt($charge) . '*' . self::amt($paid) . '*' . self::amt($resp) . '*' . $filing . '*' . $pcn . '*11',
            'NM1*QC*1*' . self::clean($patient['last']) . '*' . self::clean($patient['first']) . '****MI*' . self::clean($cov['member_id'] ?? ''),
            'DTM*050*' . date('Ymd'),
        ];
        foreach ($rows as $r) {
            $proc = 'HC:' . self::clean($r['cpt']);
            foreach (array_filter(array_map('trim', explode(',', (string)$r['mods']))) as $m) $proc .= ':' . self::clean($m);
            $body[] = 'SVC*' . $proc . '*' . self::amt($r['charge']) . '*' . self::amt($r['paid']) . '**' . $r['units'];
            $body[] = 'DTM*472*' . self::d8($claim['date_of_service']);
            foreach ($r['adj'] as [$g, $c, $a]) $body[] = "CAS*{$g}*{$c}*" . self::amt($a);
            if (!$r['denied']) $body[] = 'AMT*B6*' . self::amt($r['allowed']);
        }
        return ['ready' => true, 'message' => 'Remittance advice received.', 'x12' => $this->envelope('835', 'HP', '005010X221A1', $body, $this->ctl(), $payerId, $sender)];
    }
}
