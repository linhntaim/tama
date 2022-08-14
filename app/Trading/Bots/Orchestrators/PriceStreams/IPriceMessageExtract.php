<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Pricing\LatestPrice;

interface IPriceMessageExtract
{
    public function __invoke(array $messagePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice;
}
