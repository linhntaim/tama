<?php

namespace App\Jobs;

use App\Events\CoinIdentificationEvent;
use App\Support\Jobs\ContinuousBatchJob;

abstract class CoinIdentificationJob extends ContinuousBatchJob
{
    protected function dispatchEvent(
        string $symbol,
        string $type,
        float  $circulatingSupply,
        ?float $totalSupply,
        ?float $maxSupply,
    )
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