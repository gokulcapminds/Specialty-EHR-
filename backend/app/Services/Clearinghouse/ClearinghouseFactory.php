<?php
namespace App\Services\Clearinghouse;

use App\Services\EdiStore;

class ClearinghouseFactory {
    /** @throws \RuntimeException when the configured mode has no implementation */
    public static function make(): ClearinghouseInterface {
        $mode = EdiStore::mode();
        if ($mode === 'simulator') return new SimulatorClearinghouse();
        throw new \RuntimeException('The live clearinghouse connection is not configured. Set the clearinghouse mode back to "simulator", or add the live connector.');
    }
}
