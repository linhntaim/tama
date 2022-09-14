<?php

namespace App\Trading\Jobs;

use App\Support\Jobs\ContinuousBatchJob;
use App\Trading\Events\CoinIdentificationEvent;

abstract class CoinIdentificationJob extends ContinuousBatchJob
{
    protected function dispatchEvent(
        string $symbol,
        string $type,
        float  $circulatingSupply,
        ?float $totalSupply,
        ?float $maxSupply,
    ): void
    {
        CoinIdentificationEvent::dispatch(
            $symbol,
            $type,
            $circulatingSupply,
            $totalSupply,
            $maxSupply,
        );
    }
}
