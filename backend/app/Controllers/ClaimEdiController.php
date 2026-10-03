<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\Claim837Generator;
use App\Services\Claim837Validator;
use App\Services\Claim835Parser;
use App\Services\Clearinghouse\ClearinghouseFactory;
use App\Services\EdiResponseParsers;
use App\Services\EdiStore;

/**
 * Clearinghouse flow for a claim: generate + validate the 837, (simulated) eligibility, send, acknowledgment,
 * 835 remittance and posting. Shared claim helpers come from ClaimController (composition).
 */
class ClaimEdiController {
    private const VIEW_ROLES = ['Super Admin', 'Billing Staff', 'Doctor'];
    private const EDIT_ROLES = ['Super Admin', 'Billing Staff'];

    private ClaimController $claims;

    public function __construct() {
        $this->claims = new ClaimController();
    }

    // ------------------------------------------------------------------ helpers
    private function checkAccess(array $roles): void {
        if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
            $this->respond(['status' => 'error', 'message' => 'Access forbidden.'], 403);
            exit();
        }
    }

    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function fail(string $message, int $code = 400, array $extra = []): void {
        $this->respond(array_merge(['status' => 'error', 'message' => $message], $extra), $code);
    }

    private function input(): array {
        $in = json_decode(file_get_contents('php://input'), true);
        return is_array($in) ? $in : [];
    }

    private function claimOr404(array $params): ?array {
        $c = $this->claims->load((int)($params['id'] ?? 0));
        if (!$c) $this->fail('Claim not found.', 404);
        return $c;
    }

    /** Builds the 837 from CURRENT data (even if incomplete), validates the file text, stores both. */
    private function buildAndValidate(array $c): array {
        $ctx = $this->claims->exportContext($c);
        $file = Claim837Generator::build($ctx);
        $validation = Claim837Validator::validate($file);
        $txId = EdiStore::store(
            (int)$c['id'], (int)$c['patient_id'], '837', 'out', 'created', $file,
            ['errors' => $validation['errors'], 'warnings' => $validation['warnings'], 'checks' => $validation['checks']],
            str_pad((string)((int)$c['id'] % 1000000000), 9, '0', STR_PAD_LEFT)
        );
        Database::query("UPDATE insurance_claims SET validation_json = ?, validated_at = NOW() WHERE id = ?", [json_encode($validation), $c['id']]);
        return ['tx_id' => $txId, 'validation' => $validation, 'file' => $file];
    }

    // ------------------------------------------------------------------ steps 2-3
    // POST /api/billing/claims/{id}/edi/generate
    public function generate(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        if (!in_array($c['status'], ['Draft', 'Ready', 'Rejected'], true)) {
            $this->fail("A {$c['status']} claim can no longer be regenerated for sending.");
            return;
        }
        $r = $this->buildAndValidate($c);
        $this->claims->audit((int)$c['patient_id'], "Generate 837 for claim {$c['claim_number']} ({$r['validation']['errors']} errors, {$r['validation']['warnings']} warnings)", (int)$c['id']);
        $this->respond([
            'status'         => 'success',
            'message'        => $r['validation']['passed'] ? '837 generated - no errors found.' : '837 generated - ' . $r['validation']['errors'] . ' error(s) to fix before sending.',
            'transaction_id' => $r['tx_id'],
            'validation'     => $r['validation'],
            'file'           => $r['file'],
        ]);
    }

    // GET /api/billing/claims/{id}/edi/{tx}   (?download=1 to force a file download)
    public function viewTransaction(array $params): void {
        $this->checkAccess(self::VIEW_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        $tx = EdiStore::get((int)($params['tx'] ?? 0));
        if (!$tx || (int)$tx['claim_id'] !== (int)$c['id']) { $this->fail('File not found.', 404); return; }
        $content = EdiStore::content((int)$tx['id']);
        if ($content === null) { $this->fail('This file has no stored content.', 404); return; }
        $this->claims->audit((int)$c['patient_id'], "View {$tx['type']} file for claim {$c['claim_number']}", (int)$c['id']);   // PHI itself is never logged
        header('Content-Type: text/plain; charset=utf-8');
        header('Cache-Control: no-store');
        $name = $c['claim_number'] . '-' . $tx['type'] . '-' . $tx['id'] . '.' . strtolower($tx['type']);
        header('Content-Disposition: ' . (!empty($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $name . '.txt"');
        echo $content;
    }

    // ------------------------------------------------------------------ step 1: eligibility (270/271)
    // POST /api/billing/claims/{id}/eligibility  { scenario?: auto|inactive }
    public function eligibility(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        if ($c['status'] === 'Closed') { $this->fail('A closed claim cannot be checked.'); return; }
        $scenario = ($this->input()['scenario'] ?? 'auto') === 'inactive' ? 'inactive' : 'auto';
        try { $ch = ClearinghouseFactory::make(); } catch (\RuntimeException $e) { $this->fail($e->getMessage(), 503); return; }

        $ctx = $this->claims->exportContext($c);
        $res = $ch->checkEligibility([
            'patient' => $ctx['patient'], 'coverage' => $ctx['coverage'], 'settings' => $ctx['settings'], 'scenario' => $scenario,
            'payer' => ['name' => $c['payer_name'], 'payer_id' => $c['payer_id_code']],
            'facility' => ['name' => $ctx['facility']['facility_name'], 'npi' => $ctx['facility']['npi']],
        ]);
        $p = EdiResponseParsers::parse271($res['response_x12']);
        $reqTx = EdiStore::store((int)$c['id'], (int)$c['patient_id'], '270', 'out', 'sent', $res['request_x12'], ['scenario' => $scenario]);
        $resTx = EdiStore::store((int)$c['id'], (int)$c['patient_id'], '271', 'in', 'received', $res['response_x12'], ['status' => $p['status'], 'plan' => $p['plan']]);
        Database::query(
            "INSERT INTO eligibility_checks (patient_id, claim_id, patient_insurance_id, sequence, status, plan_name, copay, deductible, deductible_met, coinsurance_pct, message, request_tx_id, response_tx_id, checked_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$c['patient_id'], $c['id'], null, $c['sequence'], $p['status'], $p['plan'] ?: null, $p['copay'], $p['deductible'], $p['deductible_met'], $p['coinsurance_pct'], mb_substr((string)$p['message'], 0, 255) ?: null, $reqTx, $resTx, $_SESSION['user_id']]
        );
        $this->claims->audit((int)$c['patient_id'], "Eligibility check ({$p['status']}) for claim {$c['claim_number']}", (int)$c['id']);
        $this->respond([
            'status'      => 'success',
            'message'     => $p['status'] === 'Active' ? 'Coverage is active.' : ($p['status'] === 'Inactive' ? 'Coverage is NOT active - the claim cannot be sent.' : 'The payer could not confirm coverage.'),
            'eligibility' => EdiStore::latestEligibility((int)$c['patient_id'], $c['sequence']),
        ]);
    }

    // ------------------------------------------------------------------ step 4: send, then 999 + 277CA come back
    // POST /api/billing/claims/{id}/edi/send  { scenario?: auto|paid|partial|denied|rejected }
    public function send(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        if (!in_array($c['status'], ['Draft', 'Ready', 'Rejected'], true)) { $this->fail("This claim is already {$c['status']} - it cannot be sent again."); return; }
        $scenario = (string)($this->input()['scenario'] ?? 'auto');
        if (!in_array($scenario, ['auto', 'paid', 'partial', 'denied', 'rejected'], true)) $scenario = 'auto';
        try { $ch = ClearinghouseFactory::make(); } catch (\RuntimeException $e) { $this->fail($e->getMessage(), 503); return; }

        // gate 1: the claim checklist (same one that guards "Mark Ready")
        $lines = $this->claims->claimLines((int)$c['id']);
        $ctx = $this->claims->exportContext($c);
        $readiness = $this->claims->readiness($c, $lines, $ctx);
        if (!$readiness['ready_ok']) { $this->fail('The claim is not ready: ' . $this->claims->firstFailure($readiness, 'ready'), 400, ['readiness' => $readiness]); return; }

        // gate 2: eligibility, checked and current
        $elig = EdiStore::latestEligibility((int)$c['patient_id'], $c['sequence']);
        if (!$elig) { $this->fail('Check eligibility first (step 1) - coverage has not been verified.'); return; }
        if ($elig['status'] !== 'Active') { $this->fail('The last eligibility check says coverage is ' . $elig['status'] . '. The claim cannot be sent.'); return; }
        if (strtotime($elig['checked_at']) < strtotime('-7 days')) { $this->fail('The eligibility check is more than 7 days old. Please check again.'); return; }

        // gate 3: re-generate and re-validate right now, so an old validation can never be trusted
        $b = $this->buildAndValidate($c);
        if (!$b['validation']['passed']) {
            $this->fail('The 837 still has ' . $b['validation']['errors'] . ' error(s). Fix them and generate again before sending.', 400, ['validation' => $b['validation']]);
            return;
        }

        $sub = $ch->submitClaim($b['file'], $ctx + ['scenario' => $scenario]);
        $ack999 = EdiResponseParsers::parse999($sub['ack_999']);
        $ack277 = $sub['ack_277ca'] ? EdiResponseParsers::parse277($sub['ack_277ca']) : null;
        $ref = $sub['ref'];

        Database::beginTransaction();
        try {
            EdiStore::update((int)$b['tx_id'], 'sent', ['scenario' => $scenario, 'ref' => $ref]);
            EdiStore::store((int)$c['id'], (int)$c['patient_id'], '999', 'in', $ack999['accepted'] ? 'received' : 'rejected', $sub['ack_999'], $ack999);
            if ($sub['ack_277ca']) {
                EdiStore::store((int)$c['id'], (int)$c['patient_id'], '277CA', 'in', $ack277['accepted'] ? 'received' : 'rejected', $sub['ack_277ca'], $ack277);
            }
            $from = $c['status'];
            Database::query("UPDATE insurance_claims SET status = 'Submitted', submitted_at = NOW(), submission_ref = ?, clearinghouse_ref = ?, denial_code = NULL, denial_reason = NULL WHERE id = ?", [$ref, $ref, $c['id']]);
            $this->claims->history((int)$c['id'], $from, 'Submitted', "Sent to the clearinghouse ({$ch->name()}), reference {$ref}");
            if (!$ack999['accepted']) {
                $reason = 'Rejected at the syntax check (999): ' . implode('; ', $ack999['errors']);
                Database::query("UPDATE insurance_claims SET status = 'Rejected', denial_reason = ? WHERE id = ?", [mb_substr($reason, 0, 255), $c['id']]);
                $this->claims->history((int)$c['id'], 'Submitted', 'Rejected', $reason);
                $finalStatus = 'Rejected'; $message = $reason;
            } elseif ($ack277 && !$ack277['accepted']) {
                $reason = 'Rejected by the payer (277CA): ' . ($ack277['message'] ?: 'no reason given');
                Database::query("UPDATE insurance_claims SET status = 'Rejected', denial_reason = ? WHERE id = ?", [mb_substr($reason, 0, 255), $c['id']]);
                $this->claims->history((int)$c['id'], 'Submitted', 'Rejected', $reason);
                $finalStatus = 'Rejected'; $message = $reason;
            } else {
                Database::query("UPDATE insurance_claims SET status = 'Accepted', payer_claim_no = COALESCE(NULLIF(?, ''), payer_claim_no) WHERE id = ?", [$ack277['payer_claim_no'] ?? '', $c['id']]);
                $this->claims->history((int)$c['id'], 'Submitted', 'Accepted', 'Acknowledged by the payer (277CA accepted)' . (!empty($ack277['payer_claim_no']) ? ', payer claim no ' . $ack277['payer_claim_no'] : ''));
                $finalStatus = 'Accepted'; $message = 'The payer accepted the claim for processing.';
            }
            BillingController::recalcInvoice((int)$c['invoice_id']);
            Database::commit();
        } catch (\Throwable $e) {
            try { Database::rollBack(); } catch (\Throwable $x) {}
            $this->fail('The claim was sent but could not be recorded. Please refresh and check the claim history.', 500);
            return;
        }
        $this->claims->audit((int)$c['patient_id'], "Send claim {$c['claim_number']} to the clearinghouse ({$finalStatus})", (int)$c['id']);
        $this->respond([
            'status' => 'success', 'message' => $message, 'claim_status' => $finalStatus, 'ref' => $ref,
            'acknowledgment' => ['999' => $ack999, '277CA' => $ack277],
        ]);
    }

    // ------------------------------------------------------------------ step 5: receive the 835 (not posted yet)
    // POST /api/billing/claims/{id}/edi/check-remittance
    public function checkRemittance(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        if (!empty($c['pending_835_tx_id'])) {
            $pending = EdiStore::pending835($c);
            if ($pending) { $this->respond(['status' => 'success', 'ready' => true, 'message' => 'The remittance was already received and is waiting to be posted.', 'remittance' => $pending]); return; }
        }
        if (!in_array($c['status'], ['Accepted', 'Submitted', 'Appealed'], true)) {
            $this->fail($c['status'] === 'Paid' || $c['status'] === 'Partially Paid' || $c['status'] === 'Denied'
                ? 'The remittance for this claim has already been posted.'
                : "No remittance is expected while the claim is {$c['status']}. Send the claim first.");
            return;
        }
        try { $ch = ClearinghouseFactory::make(); } catch (\RuntimeException $e) { $this->fail($e->getMessage(), 503); return; }
        $sent = null;
        foreach (EdiStore::listForClaim((int)$c['id']) as $t) { if ($t['type'] === '837' && $t['status'] === 'sent') { $sent = $t; break; } }
        if (!$sent) { $this->fail('This claim was not sent through the clearinghouse, so there is no remittance to receive. Use Post Remittance to enter it manually.'); return; }

        $ctx = $this->claims->exportContext($c);
        $elig = EdiStore::latestEligibility((int)$c['patient_id'], $c['sequence']);
        // The simulated payer answers with the scenario chosen at send time; after an appeal it can be answered differently.
        $override = (string)($this->input()['scenario'] ?? '');
        $scenario = in_array($override, ['auto', 'paid', 'partial', 'denied'], true) ? $override : ($sent['summary']['scenario'] ?? 'auto');
        $res = $ch->fetchRemittance((string)($sent['summary']['ref'] ?? ''), $ctx + ['scenario' => $scenario, 'eligibility_status' => $elig['status'] ?? 'Active']);
        if (!$res['ready']) { $this->respond(['status' => 'success', 'ready' => false, 'message' => $res['message']]); return; }

        $parsed = Claim835Parser::parse($res['x12']);
        $d = Claim835Parser::describeForClaim($parsed, $this->claims->claimLines((int)$c['id']), (string)$c['claim_number']);
        $txId = EdiStore::store((int)$c['id'], (int)$c['patient_id'], '835', 'in', 'received', $res['x12'], ['trace' => $parsed['trace'], 'paid' => $d['totals']['paid'] ?? 0, 'outcome' => $d['outcome']], $parsed['trace']);
        Database::query("UPDATE insurance_claims SET pending_835_tx_id = ? WHERE id = ?", [$txId, $c['id']]);
        $this->claims->audit((int)$c['patient_id'], "Receive 835 for claim {$c['claim_number']}", (int)$c['id']);
        $d['transaction_id'] = $txId;
        $this->respond(['status' => 'success', 'ready' => true, 'message' => 'Remittance advice received. Review it, then post it to the ledger.', 'remittance' => $d]);
    }

    // POST /api/billing/claims/{id}/edi/post-835  { transaction_id }
    public function post835(array $params): void {
        $this->checkAccess(self::EDIT_ROLES);
        $c = $this->claimOr404($params);
        if (!$c) return;
        $txId = (int)($this->input()['transaction_id'] ?? 0);
        $tx = $txId ? EdiStore::get($txId) : null;
        if (!$tx || $tx['type'] !== '835' || (int)$tx['claim_id'] !== (int)$c['id']) { $this->fail('That remittance does not belong to this claim.', 404); return; }
        if ($tx['status'] === 'posted') { $this->fail('This remittance has already been posted.'); return; }
        $content = EdiStore::content($txId);
        if ($content === null) { $this->fail('The remittance file could not be read.', 500); return; }

        $lines = $this->claims->claimLines((int)$c['id']);
        $parsed = Claim835Parser::parse($content);
        $d = Claim835Parser::describeForClaim($parsed, $lines, (string)$c['claim_number']);
        if (!$d['found'] || !$d['postable']) { $this->fail('This remittance cannot be posted: ' . implode(' ', $d['problems'] ?: ['claim not found in the file.']), 400, ['remittance' => $d]); return; }
        $pc = null;
        foreach ($parsed['claims'] as $x) { if (strcasecmp($x['claim_number'], (string)$c['claim_number']) === 0) { $pc = $x; break; } }
        $map = Claim835Parser::mapToClaimLines($pc, $lines);
        $date = $parsed['payment_date'] && preg_match('/^\d{8}$/', $parsed['payment_date']) ? date('Y-m-d', strtotime($parsed['payment_date'])) : date('Y-m-d');
        if ($date > date('Y-m-d')) $date = date('Y-m-d');

        $r = $this->claims->applyRemit($c, [
            'lines' => $map['lines'], 'payment_method' => 'ACH', 'reference_no' => $parsed['trace'] ?: 'NO-TRACE', 'paid_at' => $date,
            'payer_claim_no' => $pc['payer_claim_no'], 'denial_reason' => $map['denial_reason'], 'source_note' => '835 trace ' . $parsed['trace'],
        ]);
        if (!$r['ok']) { $this->fail($r['message'], $r['code']); return; }
        EdiStore::setStatus($txId, 'posted');
        Database::query("UPDATE insurance_claims SET pending_835_tx_id = NULL WHERE id = ?", [$c['id']]);
        $this->claims->audit((int)$c['patient_id'], "Post 835 to the ledger for claim {$c['claim_number']} ({$r['new_status']})", (int)$c['id']);
        $this->respond(['status' => 'success', 'message' => $r['message'], 'new_status' => $r['new_status'], 'remittance' => $d]);
    }
}
