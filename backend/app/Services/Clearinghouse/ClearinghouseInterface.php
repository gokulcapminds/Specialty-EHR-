<?php
namespace App\Services\Clearinghouse;

/**
 * What the claim flow needs from a clearinghouse. Every method exchanges real X12 text, so the simulator and a
 * future live connection (e.g. Availity) are interchangeable. Only SimulatorClearinghouse exists today.
 */
interface ClearinghouseInterface {
    public function name(): string;

    /** @return array{request_x12: string, response_x12: string}  the 270 sent and the 271 received */
    public function checkEligibility(array $req): array;

    /**
     * Sends an 837.
     * @return array{ref: string, ack_999: string, ack_277ca: ?string}  the interchange reference and the acknowledgments
     */
    public function submitClaim(string $x12, array $ctx): array;

    /** @return array{ready: bool, message: string, x12: ?string}  the 835 once the payer has adjudicated */
    public function fetchRemittance(string $ref, array $ctx): array;
}
