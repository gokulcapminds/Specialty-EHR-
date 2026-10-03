<?php
namespace App\Services;

/**
 * Validates a generated 837P by PARSING THE FILE TEXT (not the source data), the way a clearinghouse front-end edit would,
 * then tells the user what is wrong, why, and where to fix it.
 *
 * Levels (a practical subset of the HIPAA SNIP levels):
 *   1 integrity   - envelope segments, control numbers, segment counts
 *   2 requirement - required segments / elements are present
 *   3 balancing   - claim total = sum of service lines, pointers resolve, secondary COB amounts agree
 *   4 code/format - NPI check digit, EIN, ICD-10 / CPT patterns, dates, ZIP, state, taxonomy
 *
 * This is NOT a substitute for the clearinghouse's / payer's own edits; it catches the common, fixable problems first.
 * Pure function: no DB access. Returns:
 *   ['checks' => int, 'errors' => int, 'warnings' => int, 'passed' => bool, 'issues' => [
 *       ['level' => 'error'|'warning', 'code', 'segment', 'message', 'suggestion', 'fix' => ['target', 'field', 'label']], ...]]
 * fix.target: settings | payer | claim | patient | coverage | provider | facility
 */
class Claim837Validator {
    public static function npiValid(string $n): bool {
        if (!preg_match('/^\d{10}$/', $n)) return false;
        $s = '80840' . $n;   // NPI check digit = Luhn over the number prefixed with 80840
        $sum = 0; $alt = false;
        for ($i = strlen($s) - 1; $i >= 0; $i--) {
            $d = (int)$s[$i];
            if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
            $sum += $d; $alt = !$alt;
        }
        return $sum % 10 === 0;
    }

    private static function d8Valid(string $d): bool {
        if (!preg_match('/^\d{8}$/', $d)) return false;
        return checkdate((int)substr($d, 4, 2), (int)substr($d, 6, 2), (int)substr($d, 0, 4));
    }

    public static function validate(string $x12): array {
        $issues = [];
        $checks = 0;
        $add = function (string $level, string $code, string $seg, string $msg, string $sug, array $fix) use (&$issues) {
            $issues[] = ['level' => $level, 'code' => $code, 'segment' => $seg, 'message' => $msg, 'suggestion' => $sug, 'fix' => $fix + ['field' => '', 'label' => '']];
        };
        // check(): counts every assertion; records an issue when it fails
        $check = function (bool $ok, string $level, string $code, string $seg, string $msg, string $sug, array $fix) use (&$checks, $add) {
            $checks++;
            if (!$ok) $add($level, $code, $seg, $msg, $sug, $fix);
        };
        $FIX = [
            'settings' => ['target' => 'settings', 'label' => 'Billing > Insurance Claims > Manage Payers > 837 submitter settings'],
            'payer'    => ['target' => 'payer', 'label' => 'Billing > Insurance Claims > Manage Payers'],
            'claim'    => ['target' => 'claim', 'label' => 'Claim > Edit Claim'],
            'patient'  => ['target' => 'patient', 'label' => 'Patients > open the patient > edit demographics'],
            'coverage' => ['target' => 'coverage', 'label' => 'Patients > edit the patient > Insurance step'],
            'provider' => ['target' => 'provider', 'label' => 'Administration > Users > the rendering provider\'s profile'],
            'facility' => ['target' => 'facility', 'label' => 'Administration > Facilities > the billing facility'],
        ];
        $fx = fn(string $t, string $field = '', string $label = '') => ['target' => $FIX[$t]['target'], 'field' => $field, 'label' => $label ?: $FIX[$t]['label']];

        // ---- parse ----
        $raw = preg_split('/~/', $x12);
        $isaRaw = isset($raw[0]) ? trim($raw[0], "\r\n") : '';
        $segs = [];
        foreach ($raw as $r) {
            $r = trim($r, "\r\n ");
            if ($r === '') continue;
            $segs[] = explode('*', $r);
        }
        $e = fn(array $seg, int $i) => trim((string)($seg[$i] ?? ''));
        $all = fn(string $tag) => array_values(array_filter($segs, fn($s) => $s[0] === $tag));
        $firstBy = function (string $tag, ?callable $where = null) use ($segs) {
            foreach ($segs as $s) { if ($s[0] === $tag && (!$where || $where($s))) return $s; }
            return null;
        };
        $nm1 = fn(string $q) => $firstBy('NM1', fn($s) => ($s[1] ?? '') === $q);

        // ============ Level 1: integrity ============
        $check(count($segs) > 0 && $segs[0][0] === 'ISA', 'error', 'ENV_ISA', 'ISA', 'The file does not start with an ISA segment.', 'Regenerate the 837 file.', $fx('claim'));
        $check(strlen($isaRaw) === 105, 'error', 'ISA_LENGTH', 'ISA', 'The ISA header is not the required fixed length (' . strlen($isaRaw) . ' instead of 105 characters).', 'The submitter or receiver ID is longer than 15 characters or contains a separator character. Correct the IDs in the 837 submitter settings.', $fx('settings'));
        $order = array_map(fn($s) => $s[0], $segs);
        $iIsa = array_search('ISA', $order, true); $iGs = array_search('GS', $order, true); $iSt = array_search('ST', $order, true);
        $iSe = array_search('SE', $order, true); $iGe = array_search('GE', $order, true); $iIea = array_search('IEA', $order, true);
        $envOk = $iIsa !== false && $iGs !== false && $iSt !== false && $iSe !== false && $iGe !== false && $iIea !== false && $iIsa < $iGs && $iGs < $iSt && $iSt < $iSe && $iSe < $iGe && $iGe < $iIea;
        $check($envOk, 'error', 'ENV_ORDER', 'ISA/GS/ST/SE/GE/IEA', 'The envelope segments (ISA, GS, ST, SE, GE, IEA) are missing or out of order.', 'Regenerate the 837 file. If it happens again, report it - the file generator has a fault.', $fx('claim'));
        if ($envOk) {
            $check($e($segs[$iIsa], 13) === $e($segs[$iIea], 2), 'error', 'ISA_CONTROL', 'ISA13 / IEA02', 'The interchange control numbers in ISA and IEA do not match.', 'Regenerate the 837 file.', $fx('claim'));
            $check($e($segs[$iGs], 6) === $e($segs[$iGe], 2), 'error', 'GS_CONTROL', 'GS06 / GE02', 'The group control numbers in GS and GE do not match.', 'Regenerate the 837 file.', $fx('claim'));
            $check($e($segs[$iSt], 2) === $e($segs[$iSe], 2), 'error', 'ST_CONTROL', 'ST02 / SE02', 'The transaction control numbers in ST and SE do not match.', 'Regenerate the 837 file.', $fx('claim'));
            $check((int)$e($segs[$iSe], 1) === ($iSe - $iSt + 1), 'error', 'SE_COUNT', 'SE01', 'SE01 says ' . $e($segs[$iSe], 1) . ' segments but the transaction contains ' . ($iSe - $iSt + 1) . '.', 'Regenerate the 837 file.', $fx('claim'));
            $check($e($segs[$iGe], 1) === '1' && $e($segs[$iIea], 1) === '1', 'error', 'ENV_COUNTS', 'GE01 / IEA01', 'GE01 and IEA01 must each be 1 (one transaction, one group).', 'Regenerate the 837 file.', $fx('claim'));
        }

        // ============ Level 2: submitter / receiver ============
        $sub = $nm1('41');
        $check($sub !== null && $e($sub, 3) !== '', 'error', 'SUBMITTER_NAME', 'NM1*41 (NM103)', 'Submitter name is missing.', 'Complete the billing facility\'s legal entity name.', $fx('facility', 'legal_entity_name'));
        $check($sub !== null && $e($sub, 9) !== '', 'error', 'SUBMITTER_ID', 'NM1*41 (NM109)', 'Submitter ID is missing.', 'Enter the submitter ID your clearinghouse assigned to the clinic.', $fx('settings', 'billing_submitter_id'));
        $per = $firstBy('PER', fn($s) => ($s[1] ?? '') === 'IC');
        $check($per !== null && $e($per, 2) !== '', 'error', 'SUBMITTER_CONTACT', 'PER*IC (PER02)', 'Submitter contact name is missing.', 'Enter a billing contact name.', $fx('settings', 'billing_contact_name'));
        $check($per !== null && preg_match('/^\d{10}$/', $e($per, 4)), 'error', 'SUBMITTER_PHONE', 'PER*IC (PER04)', 'Submitter contact phone is missing or not 10 digits.', 'Enter a 10-digit billing contact phone.', $fx('settings', 'billing_contact_phone'));
        $rcv = $nm1('40');
        $check($rcv !== null && $e($rcv, 9) !== '', 'error', 'RECEIVER_ID', 'NM1*40 (NM109)', 'Receiver ID is missing.', 'Enter the receiver ID your clearinghouse gave you.', $fx('settings', 'billing_receiver_id'));

        // ---- billing provider (facility) ----
        $bill = $nm1('85');
        $check($bill !== null && $e($bill, 3) !== '', 'error', 'BILLING_NAME', 'NM1*85 (NM103)', 'Billing provider name is missing.', 'Complete the facility\'s legal entity or facility name.', $fx('facility', 'legal_entity_name'));
        $bnpi = $bill ? $e($bill, 9) : '';
        $check($bnpi !== '', 'error', 'BILLING_NPI_MISSING', 'NM1*85 (NM109)', 'Billing provider NPI is missing.', 'Enter the facility\'s 10-digit NPI.', $fx('facility', 'npi'));
        if ($bnpi !== '') {
            $check(self::npiValid($bnpi), 'error', 'BILLING_NPI_INVALID', 'NM1*85 (NM109)', "Billing provider NPI {$bnpi} is not a valid NPI (10 digits with a correct check digit).", 'Re-check the NPI on the NPPES registry and correct it on the facility record.', $fx('facility', 'npi'));
        }
        $ein = $firstBy('REF', fn($s) => ($s[1] ?? '') === 'EI');
        $check($ein !== null && preg_match('/^\d{9}$/', $e($ein, 2)), 'error', 'BILLING_EIN', 'REF*EI (REF02)', 'Billing provider tax ID (EIN) is missing or is not 9 digits' . ($ein ? ' (found "' . $e($ein, 2) . '")' : '') . '.', 'Enter the 9-digit EIN on the facility record (dashes are removed automatically).', $fx('facility', 'tax_id_ein'));
        // address N3/N4 directly after NM1*85
        $idxBill = $bill ? array_search($bill, $segs, true) : false;
        $n3b = ($idxBill !== false && ($segs[$idxBill + 1][0] ?? '') === 'N3') ? $segs[$idxBill + 1] : null;
        $n4b = ($idxBill !== false && ($segs[$idxBill + 2][0] ?? '') === 'N4') ? $segs[$idxBill + 2] : null;
        $check($n3b !== null && $e($n3b, 1) !== '', 'error', 'BILLING_ADDR', 'N3 (billing provider)', 'Billing provider street address is missing.', 'Enter the facility address.', $fx('facility', 'address'));
        $check($n4b !== null && $e($n4b, 1) !== '' && preg_match('/^[A-Z]{2}$/', $e($n4b, 2)) && preg_match('/^(\d{5}|\d{9})$/', $e($n4b, 3)), 'error', 'BILLING_CSZ', 'N4 (billing provider)', 'Billing provider city, 2-letter state and ZIP are missing or not valid.', 'Enter the city, a 2-letter state code (e.g. IL) and a 5- or 9-digit ZIP on the facility record.', $fx('facility', 'city/state/zip'));
        if ($n4b !== null && preg_match('/^\d{5}$/', $e($n4b, 3))) {
            $add('warning', 'BILLING_ZIP9', 'N4 (billing provider)', 'Billing provider ZIP has only 5 digits. Many payers require ZIP+4 for the billing provider.', 'Enter the 9-digit ZIP (ZIP+4) on the facility record.', $fx('facility', 'zip_code'));
            $checks++;
        }
        $prvBi = $firstBy('PRV', fn($s) => ($s[1] ?? '') === 'BI');
        $check($prvBi !== null && preg_match('/^[A-Z0-9]{10}$/', $e($prvBi, 3)), 'error', 'BILLING_TAXONOMY', 'PRV*BI (PRV03)', 'Provider taxonomy code is missing or not 10 letters/digits.', 'Enter the provider\'s 10-character taxonomy code (e.g. 207RC0000X for Cardiology).', $fx('provider', 'taxonomy_code'));

        // ---- subscriber / patient / payer ----
        $sbr = $firstBy('SBR');
        $check($sbr !== null && in_array($e($sbr, 1), ['P', 'S'], true), 'error', 'SBR', 'SBR (SBR01)', 'Subscriber (payer sequence) segment is missing or not P/S.', 'Regenerate the 837 file.', $fx('claim'));
        $il = $nm1('IL');
        $check($il !== null && $e($il, 3) !== '' && $e($il, 4) !== '', 'error', 'SUBSCRIBER_NAME', 'NM1*IL (NM103/NM104)', 'Subscriber first or last name is missing.', 'Complete the subscriber name (or the patient name when the patient is the subscriber).', $fx('coverage', 'subscriber_name'));
        $check($il !== null && $e($il, 9) !== '', 'error', 'MEMBER_ID', 'NM1*IL (NM109)', 'Member / policy ID is missing.', 'Enter the member ID from the insurance card on the patient\'s insurance.', $fx('coverage', 'member_id'));
        $idxIl = $il ? array_search($il, $segs, true) : false;
        $n3s = ($idxIl !== false && ($segs[$idxIl + 1][0] ?? '') === 'N3') ? $segs[$idxIl + 1] : null;
        $n4s = ($idxIl !== false && ($segs[$idxIl + 2][0] ?? '') === 'N4') ? $segs[$idxIl + 2] : null;
        $dmgs = ($idxIl !== false && ($segs[$idxIl + 3][0] ?? '') === 'DMG') ? $segs[$idxIl + 3] : null;
        $check($n3s !== null && $e($n3s, 1) !== '', 'error', 'SUBSCRIBER_ADDR', 'N3 (subscriber)', 'Subscriber street address is missing.', 'Enter the patient\'s street address.', $fx('patient', 'address'));
        $check($n4s !== null && $e($n4s, 1) !== '' && preg_match('/^[A-Z]{2}$/', strtoupper($e($n4s, 2))) && preg_match('/^(\d{5}|\d{9})$/', $e($n4s, 3)), 'error', 'SUBSCRIBER_CSZ', 'N4 (subscriber)', 'Subscriber city, 2-letter state and ZIP are missing or not valid.', 'Enter the patient\'s city, a 2-letter state code and a 5- or 9-digit ZIP.', $fx('patient', 'city/state/zip'));
        $dobOk = $dmgs !== null && self::d8Valid($e($dmgs, 2));
        $check($dobOk, 'error', 'SUBSCRIBER_DOB', 'DMG (subscriber DOB)', 'Subscriber date of birth is missing or not a valid date.', 'Enter the date of birth.', $fx('patient', 'dob'));
        if ($dobOk) {
            $dob = $e($dmgs, 2);
            $check($dob <= date('Ymd') && $dob >= '19000101', 'error', 'SUBSCRIBER_DOB_RANGE', 'DMG (subscriber DOB)', 'Date of birth is in the future or before 1900.', 'Correct the date of birth.', $fx('patient', 'dob'));
        }
        $isDependent = $firstBy('HL', fn($s) => ($s[3] ?? '') === '23') !== null;
        if (!$isDependent) {
            $check($dmgs !== null && in_array($e($dmgs, 3), ['M', 'F'], true), 'error', 'SUBSCRIBER_SEX', 'DMG (subscriber sex)', 'Sex is missing or unknown (must be M or F for the claim).', 'Set the patient\'s sex.', $fx('patient', 'gender'));
        } elseif ($dmgs !== null && $e($dmgs, 3) === 'U') {
            // the policy holder is someone else and the app does not store their sex: warn, do not block
            $add('warning', 'SUBSCRIBER_SEX_UNKNOWN', 'DMG (subscriber sex)', 'The policy holder\'s sex is not on file (sent as unknown). Some payers reject this.', 'If the payer rejects it, add the policy holder\'s sex to the insurance record.', $fx('coverage', 'subscriber_gender'));
            $checks++;
        }
        $pr = $nm1('PR');
        $check($pr !== null && $e($pr, 3) !== '', 'error', 'PAYER_NAME', 'NM1*PR (NM103)', 'Payer name is missing.', 'Choose the payer on the claim.', $fx('claim', 'payer_name'));
        $pid = $pr ? $e($pr, 9) : '';
        $check($pid !== '', 'error', 'PAYER_ID_MISSING', 'NM1*PR (NM109)', 'Payer ID is missing.', 'Enter the payer ID for this payer (from the payer\'s claims page or your clearinghouse payer list).', $fx('payer', 'payer_id_code'));
        if ($pid !== '') $check((bool)preg_match('/^[A-Z0-9\-]{2,15}$/', $pid), 'error', 'PAYER_ID_FORMAT', 'NM1*PR (NM109)', "Payer ID \"{$pid}\" is not in a valid format.", 'Use the payer ID exactly as listed by your clearinghouse (letters and digits, up to 15).', $fx('payer', 'payer_id_code'));
        if ($firstBy('HL', fn($s) => ($s[3] ?? '') === '23') !== null) {
            $qc = $nm1('QC');
            $check($qc !== null && $e($qc, 3) !== '' && $e($qc, 4) !== '', 'error', 'PATIENT_NAME', 'NM1*QC', 'Patient name is missing for the dependent loop.', 'Complete the patient\'s name.', $fx('patient', 'name'));
            $check($firstBy('PAT') !== null, 'error', 'PATIENT_REL', 'PAT (PAT01)', 'Patient relationship to the subscriber is missing.', 'Set the subscriber relationship on the patient\'s insurance.', $fx('coverage', 'subscriber_relationship'));
            $idxQc = $qc ? array_search($qc, $segs, true) : false;
            $dmgQc = ($idxQc !== false && ($segs[$idxQc + 3][0] ?? '') === 'DMG') ? $segs[$idxQc + 3] : null;
            $check($dmgQc !== null && self::d8Valid($e($dmgQc, 2)), 'error', 'PATIENT_DOB', 'DMG (patient DOB)', 'Patient date of birth is missing or not a valid date.', 'Enter the patient\'s date of birth.', $fx('patient', 'dob'));
            $check($dmgQc !== null && in_array($e($dmgQc, 3), ['M', 'F'], true), 'error', 'PATIENT_SEX', 'DMG (patient sex)', 'Patient sex is missing or unknown (must be M or F).', 'Set the patient\'s sex.', $fx('patient', 'gender'));
        }

        // ---- claim / diagnoses ----
        $clm = $firstBy('CLM');
        $check($clm !== null && $e($clm, 1) !== '', 'error', 'CLM_NUMBER', 'CLM (CLM01)', 'Claim number is missing.', 'Regenerate the 837 file.', $fx('claim'));
        $clmAmt = $clm ? (float)$e($clm, 2) : 0.0;
        $check($clmAmt > 0, 'error', 'CLM_AMOUNT', 'CLM (CLM02)', 'Claim total is zero or missing.', 'Add charges to the invoice / claim.', $fx('claim'));
        $hi = $firstBy('HI');
        $dx = [];
        if ($hi) {
            foreach (array_slice($hi, 1) as $el) {
                $parts = explode(':', $el);
                if (($parts[1] ?? '') !== '') $dx[] = $parts[1];
            }
        }
        $check(count($dx) > 0, 'error', 'HI_MISSING', 'HI', 'The claim has no diagnosis codes.', 'Add an ICD-10 diagnosis code to at least one service line.', $fx('claim', 'icd10_code'));
        foreach ($dx as $code) {
            $check((bool)preg_match('/^[A-TV-Z][0-9][0-9AB][0-9A-TV-Z]{0,4}$/', $code), 'error', 'ICD10_FORMAT', 'HI (' . $code . ')', "Diagnosis code \"{$code}\" is not a valid ICD-10-CM format.", 'Correct the ICD-10 code on the invoice line (letter, two digits, then up to 4 more characters; no decimal point in the file).', $fx('claim', 'icd10_code'));
        }

        // ---- rendering provider ----
        $rend = $nm1('82');
        $check($rend !== null && $e($rend, 3) !== '' && $e($rend, 4) !== '', 'error', 'RENDERING_NAME', 'NM1*82', 'Rendering provider name is missing.', 'Complete the provider\'s first and last name.', $fx('provider', 'name'));
        $rnpi = $rend ? $e($rend, 9) : '';
        $check($rnpi !== '', 'error', 'RENDERING_NPI_MISSING', 'NM1*82 (NM109)', 'Rendering provider NPI is missing.', 'Enter the provider\'s 10-digit NPI on their profile.', $fx('provider', 'npi'));
        if ($rnpi !== '') $check(self::npiValid($rnpi), 'error', 'RENDERING_NPI_INVALID', 'NM1*82 (NM109)', "Rendering provider NPI {$rnpi} is not a valid NPI (10 digits with a correct check digit).", 'Re-check the NPI on the NPPES registry and correct it on the provider profile.', $fx('provider', 'npi'));
        $prvPe = $firstBy('PRV', fn($s) => ($s[1] ?? '') === 'PE');
        $check($prvPe !== null && preg_match('/^[A-Z0-9]{10}$/', $e($prvPe, 3)), 'error', 'RENDERING_TAXONOMY', 'PRV*PE (PRV03)', 'Rendering provider taxonomy code is missing or not 10 letters/digits.', 'Enter the provider\'s 10-character taxonomy code.', $fx('provider', 'taxonomy_code'));

        // ---- service lines ----
        $lines = [];
        for ($i = 0; $i < count($segs); $i++) {
            if ($segs[$i][0] !== 'SV1') continue;
            $lines[] = ['sv1' => $segs[$i], 'dtp' => null, 'svd' => null];
            $j = count($lines) - 1;
            for ($k = $i + 1; $k < count($segs) && !in_array($segs[$k][0], ['LX', 'SE'], true); $k++) {
                if ($segs[$k][0] === 'DTP' && $e($segs[$k], 1) === '472') $lines[$j]['dtp'] = $segs[$k];
                if ($segs[$k][0] === 'SVD') $lines[$j]['svd'] = $segs[$k];
            }
        }
        $check(count($lines) > 0, 'error', 'NO_LINES', 'LX/SV1', 'The claim has no service lines.', 'Add at least one charge line to the claim.', $fx('claim'));
        $sumSv1 = 0.0; $sumSvd = 0.0; $ln = 0;
        foreach ($lines as $l) {
            $ln++;
            $sv = $l['sv1'];
            $proc = explode(':', $e($sv, 1));
            $cpt = $proc[1] ?? '';
            $tag = "line {$ln}";
            $check($cpt !== '' && (bool)preg_match('/^(\d{5}|[A-V]\d{4})$/', $cpt), 'error', 'CPT_FORMAT', "SV1 ({$tag})", "Procedure code \"{$cpt}\" is missing or not a valid CPT/HCPCS format.", 'Enter a 5-digit CPT code (or a HCPCS code like G0439) on the invoice line.', $fx('claim', 'cpt_code'));
            foreach (array_slice($proc, 2) as $mod) {
                $check((bool)preg_match('/^[A-Z0-9]{2}$/', $mod), 'error', 'MODIFIER', "SV1 ({$tag})", "Modifier \"{$mod}\" must be exactly 2 letters/digits.", 'Fix the modifier in Edit Claim.', $fx('claim', 'modifiers'));
            }
            $charge = (float)$e($sv, 2);
            $sumSv1 += $charge;
            $check($charge > 0, 'error', 'LINE_CHARGE', "SV1 ({$tag})", 'Line charge is zero or missing.', 'Enter a charge for the line.', $fx('claim'));
            $check($e($sv, 3) === 'UN' && (int)$e($sv, 4) > 0 && (string)(int)$e($sv, 4) === $e($sv, 4), 'error', 'LINE_UNITS', "SV1 ({$tag})", 'Line units must be a whole number greater than zero.', 'Correct the quantity on the invoice line.', $fx('claim'));
            $ptrs = array_filter(explode(':', $e($sv, 7)), 'strlen');
            $check(count($ptrs) > 0, 'error', 'DX_POINTER_MISSING', "SV1 ({$tag})", 'The line has no diagnosis pointer.', 'Add a diagnosis pointer (A-L) in Edit Claim, and make sure the line has an ICD-10 code.', $fx('claim', 'dx_pointer'));
            foreach ($ptrs as $p) {
                $check((int)$p >= 1 && (int)$p <= count($dx), 'error', 'DX_POINTER_RANGE', "SV1 ({$tag})", "Diagnosis pointer {$p} does not match any diagnosis on the claim.", 'Fix the diagnosis pointer in Edit Claim.', $fx('claim', 'dx_pointer'));
            }
            $dos = $l['dtp'] ? $e($l['dtp'], 3) : '';
            $check(self::d8Valid($dos), 'error', 'DOS_MISSING', "DTP*472 ({$tag})", 'Date of service is missing or not a valid date.', 'Regenerate the file; if it persists, check the encounter date.', $fx('claim'));
            if (self::d8Valid($dos)) {
                $check($dos <= date('Ymd'), 'error', 'DOS_FUTURE', "DTP*472 ({$tag})", "Date of service {$dos} is in the future.", 'Correct the encounter / invoice date.', $fx('claim'));
                if ($dos < date('Ymd', strtotime('-365 days'))) {
                    $add('warning', 'DOS_TIMELY', "DTP*472 ({$tag})", "Date of service {$dos} is more than a year old; many payers deny claims filed after their timely-filing limit.", 'Check the payer\'s timely filing limit before sending.', $fx('claim'));
                    $checks++;
                }
            }
            if ($l['svd']) $sumSvd += (float)$e($l['svd'], 2);
        }

        // ============ Level 3: balancing ============
        if ($clm && count($lines)) {
            $check(abs($clmAmt - $sumSv1) < 0.005, 'error', 'CLM_BALANCE', 'CLM02 vs SV1', 'The claim total (' . number_format($clmAmt, 2) . ') does not equal the sum of the service lines (' . number_format($sumSv1, 2) . ').', 'Check the invoice lines and regenerate the file.', $fx('claim'));
        }
        $isSecondary = $sbr && $e($sbr, 1) === 'S';
        if ($isSecondary) {
            $amtD = $firstBy('AMT', fn($s) => ($s[1] ?? '') === 'D');
            $check($amtD !== null, 'error', 'COB_AMT', 'AMT*D', 'Secondary claim is missing the amount the primary payer paid.', 'Post the primary remittance before billing the secondary payer.', $fx('claim'));
            if ($amtD) $check(abs((float)$e($amtD, 2) - $sumSvd) < 0.005, 'error', 'COB_BALANCE', 'AMT*D vs SVD', 'The primary payer total does not equal the sum of the line-level primary payments.', 'Reverse and re-post the primary remittance, then recreate the secondary claim.', $fx('claim'));
        }

        // ---- summarise ----
        usort($issues, fn($a, $b) => ($a['level'] === $b['level']) ? 0 : ($a['level'] === 'error' ? -1 : 1));
        $errors = count(array_filter($issues, fn($i) => $i['level'] === 'error'));
        $warnings = count($issues) - $errors;
        return ['checks' => $checks, 'errors' => $errors, 'warnings' => $warnings, 'passed' => $errors === 0, 'issues' => $issues];
    }
}
