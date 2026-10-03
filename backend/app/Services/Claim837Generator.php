<?php
namespace App\Services;

/**
 * Builds a basic ANSI X12 5010 837P (005010X222A1) professional claim file from an already-assembled claim context.
 * Pure function of its input (no DB access) so it can be checked structurally without a payer or clearinghouse.
 *
 * Honest scope: covers the common single-claim professional case (primary or secondary, self or dependent patient).
 * It has NOT been validated against a real payer/clearinghouse - run it through the clearinghouse's validator first.
 *
 * $ctx keys: claim, lines, patient, coverage, provider, facility, settings, payer, parent (secondary only:
 *            ['claim' =>, 'lines' =>, 'coverage' =>, 'payer' =>]).
 */
class Claim837Generator {
    private static function clean($v): string {
        return strtoupper(trim(preg_replace('/[*~:^|\\\\]+/', ' ', (string)$v)));
    }
    private static function digits($v): string {
        return preg_replace('/\D/', '', (string)$v);
    }
    private static function amt($v): string {
        $s = number_format((float)$v, 2, '.', '');
        return rtrim(rtrim($s, '0'), '.') === '' ? '0' : rtrim(rtrim($s, '0'), '.');
    }
    private static function d8($date): string {
        return $date ? date('Ymd', strtotime((string)$date)) : '';
    }
    private static function sex($g): string {
        $g = strtolower(trim((string)$g));
        return $g === 'male' ? 'M' : ($g === 'female' ? 'F' : 'U');
    }
    private static function isSelf(array $cov): bool {
        $r = strtolower(trim((string)($cov['relationship'] ?? '')));
        return $r === '' || $r === 'self' || $r === 'patient';
    }
    private static function relCode(array $cov): string {
        $r = strtolower(trim((string)($cov['relationship'] ?? '')));
        if (strpos($r, 'spouse') !== false) return '01';
        if (strpos($r, 'child') !== false) return '19';
        if (strpos($r, 'partner') !== false) return '53';
        return 'G8';
    }

    /** Returns the complete interchange (ISA ... IEA) as a string, one segment per line, each terminated by "~". */
    public static function build(array $ctx): string {
        $claim = $ctx['claim'];
        $lines = $ctx['lines'];
        $pt = $ctx['patient'];
        $cov = $ctx['coverage'];
        $prov = $ctx['provider'];
        $fac = $ctx['facility'];
        $set = $ctx['settings'];
        $payer = $ctx['payer'] ?? [];
        $parent = $ctx['parent'] ?? null;
        $isSecondary = ($claim['sequence'] ?? 'Primary') === 'Secondary';

        $sender = self::clean($set['billing_submitter_id'] ?? '');
        $receiver = self::clean($set['billing_receiver_id'] ?? '');
        $ctl = str_pad((string)((int)$claim['id'] % 1000000000), 9, '0', STR_PAD_LEFT);
        $ymd = date('Ymd');
        $hm = date('Hi');

        // Diagnosis list, in the order the claim lines reference them (A..L -> 1..12)
        $dx = [];
        foreach ($lines as $l) {
            $code = str_replace('.', '', self::clean($l['icd10_code'] ?? ''));
            if ($code !== '' && !in_array($code, $dx, true) && count($dx) < 12) $dx[] = $code;
        }

        $st = [];   // segments between ST and SE
        $st[] = 'ST*837*0001*005010X222A1';
        $st[] = 'BHT*0019*00*' . self::clean($claim['claim_number']) . '*' . $ymd . '*' . $hm . '*CH';
        $st[] = 'NM1*41*2*' . self::clean($fac['legal_entity_name'] ?: $fac['facility_name']) . '*****46*' . $sender;
        $st[] = 'PER*IC*' . self::clean($set['billing_contact_name'] ?? '') . '*TE*' . self::digits($set['billing_contact_phone'] ?? '');
        $st[] = 'NM1*40*2*' . self::clean($payer['name'] ?? $claim['payer_name']) . '*****46*' . $receiver;

        // 2000A billing provider (the facility / legal entity)
        $st[] = 'HL*1**20*1';
        $st[] = 'PRV*BI*PXC*' . self::clean($prov['taxonomy_code'] ?? '');
        $st[] = 'NM1*85*2*' . self::clean($fac['legal_entity_name'] ?: $fac['facility_name']) . '*****XX*' . self::digits($fac['npi']);
        $st[] = 'N3*' . self::clean($fac['address_line1'] ?: $fac['address']);
        $st[] = 'N4*' . self::clean($fac['city']) . '*' . self::clean($fac['state']) . '*' . self::digits($fac['zip_code'] ?: $fac['postal_code']);
        $st[] = 'REF*EI*' . self::digits($fac['tax_id_ein']);

        // 2000B subscriber
        $self = self::isSelf($cov);
        $subFirst = $self ? $pt['first'] : ($cov['subscriber_first'] ?? '');
        $subLast = $self ? $pt['last'] : ($cov['subscriber_last'] ?? '');
        $subDob = $self ? $pt['dob'] : ($cov['subscriber_dob'] ?? '');
        $filing = $payer['claim_filing_code'] ?? 'CI';
        $st[] = 'HL*2*1*22*' . ($self ? '0' : '1');
        $st[] = 'SBR*' . ($isSecondary ? 'S' : 'P') . '*' . ($self ? '18' : '') . '*' . self::clean($cov['group_no'] ?? '') . '*' . self::clean($cov['plan_name'] ?? '') . '****' . '*' . $filing;
        $st[] = 'NM1*IL*1*' . self::clean($subLast) . '*' . self::clean($subFirst) . '****MI*' . self::clean($cov['member_id'] ?? '');
        $st[] = 'N3*' . self::clean($pt['address']);
        $st[] = 'N4*' . self::clean($pt['city']) . '*' . self::clean($pt['state']) . '*' . self::digits($pt['zip']);
        $st[] = 'DMG*D8*' . self::d8($subDob) . '*' . ($self ? self::sex($pt['gender']) : 'U');
        $st[] = 'NM1*PR*2*' . self::clean($payer['name'] ?? $claim['payer_name']) . '*****PI*' . self::clean($claim['payer_id_code']);

        // 2000C patient (only when the patient is not the subscriber)
        if (!$self) {
            $st[] = 'HL*3*2*23*0';
            $st[] = 'PAT*' . self::relCode($cov);
            $st[] = 'NM1*QC*1*' . self::clean($pt['last']) . '*' . self::clean($pt['first']);
            $st[] = 'N3*' . self::clean($pt['address']);
            $st[] = 'N4*' . self::clean($pt['city']) . '*' . self::clean($pt['state']) . '*' . self::digits($pt['zip']);
            $st[] = 'DMG*D8*' . self::d8($pt['dob']) . '*' . self::sex($pt['gender']);
        }

        // 2300 claim
        $st[] = 'CLM*' . self::clean($claim['claim_number']) . '*' . self::amt($ctx['claim_total'] ?? $claim['billed_amount']) . '***11:B:1*Y*A*Y*Y';
        if ($dx) {
            $his = [];
            foreach ($dx as $i => $code) $his[] = ($i === 0 ? 'ABK' : 'ABF') . ':' . $code;
            $st[] = 'HI*' . implode('*', $his);
        }

        // 2320 other subscriber information (secondary claims: what the primary payer did)
        if ($isSecondary && $parent) {
            $pc = $parent['coverage'];
            $pSelf = self::isSelf($pc);
            $pFirst = $pSelf ? $pt['first'] : ($pc['subscriber_first'] ?? '');
            $pLast = $pSelf ? $pt['last'] : ($pc['subscriber_last'] ?? '');
            $pPaid = 0.0;
            foreach ($parent['lines'] as $pl) $pPaid += (float)$pl['paid'];
            $st[] = 'SBR*P*' . ($pSelf ? '18' : '') . '*' . self::clean($pc['group_no'] ?? '') . '*' . self::clean($pc['plan_name'] ?? '') . '****' . '*' . ($parent['payer']['claim_filing_code'] ?? 'CI');
            $st[] = 'AMT*D*' . self::amt($pPaid);
            $st[] = 'OI***Y***Y';
            $st[] = 'NM1*IL*1*' . self::clean($pLast) . '*' . self::clean($pFirst) . '****MI*' . self::clean($pc['member_id'] ?? '');
            $st[] = 'NM1*PR*2*' . self::clean($parent['claim']['payer_name']) . '*****PI*' . self::clean($parent['claim']['payer_id_code']);
        }

        // 2310B rendering provider
        $st[] = 'NM1*82*1*' . self::clean($prov['last_name']) . '*' . self::clean($prov['first_name']) . '****XX*' . self::digits($prov['npi']);
        $st[] = 'PRV*PE*PXC*' . self::clean($prov['taxonomy_code'] ?? '');

        // 2400 service lines
        $n = 0;
        foreach ($lines as $l) {
            $n++;
            $orig = $l;   // for a secondary claim the SV1 charge is the ORIGINAL line charge (from the primary), SVD carries the primary payment
            $parentLine = null;
            if ($isSecondary && $parent) {
                foreach ($parent['lines'] as $pl) {
                    if ((int)$pl['invoice_line_item_id'] === (int)$l['invoice_line_item_id']) { $parentLine = $pl; break; }
                }
                if ($parentLine) $orig['charge'] = $parentLine['charge'];
            }
            $proc = 'HC:' . self::clean($l['cpt_code']);
            foreach (array_filter(array_map('trim', explode(',', (string)($l['modifiers'] ?? '')))) as $m) $proc .= ':' . self::clean($m);
            $ptrs = [];
            foreach (str_split(preg_replace('/[^A-L]/', '', strtoupper((string)($l['dx_pointer'] ?? '')))) as $letter) $ptrs[] = (string)(ord($letter) - 64);
            $st[] = 'LX*' . $n;
            $st[] = 'SV1*' . $proc . '*' . self::amt($orig['charge']) . '*UN*' . (int)$l['units'] . '***' . implode(':', $ptrs);
            $st[] = 'DTP*472*D8*' . self::d8($claim['date_of_service']);
            if ($isSecondary && $parentLine) {
                $st[] = 'SVD*' . self::clean($parent['claim']['payer_id_code']) . '*' . self::amt($parentLine['paid']) . '*' . $proc . '**' . (int)$l['units'];
                if ((float)$parentLine['adjustment'] > 0) $st[] = 'CAS*CO*45*' . self::amt($parentLine['adjustment']);
                $st[] = 'DTP*573*D8*' . $ymd;
            }
        }

        $st[] = 'SE*' . (count($st) + 1) . '*0001';

        $isa = 'ISA*00*' . str_repeat(' ', 10) . '*00*' . str_repeat(' ', 10) . '*ZZ*' . str_pad(substr($sender, 0, 15), 15) . '*ZZ*' . str_pad(substr($receiver, 0, 15), 15)
            . '*' . date('ymd') . '*' . $hm . '*^*00501*' . $ctl . '*0*P*:';
        $gs = 'GS*HC*' . $sender . '*' . $receiver . '*' . $ymd . '*' . $hm . '*' . (int)$ctl . '*X*005010X222A1';
        $segments = array_merge([$isa, $gs], $st, ['GE*1*' . (int)$ctl, 'IEA*1*' . $ctl]);
        return implode("~\n", $segments) . "~\n";
    }
}
