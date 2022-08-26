<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Pricing\LatestPrice;

class BinancePriceStreamMessageExtractor implements IPriceMessageExtract
{
    public function __invoke(array $messagePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice
    {
        $ticker = $interval = null;
        if (!isset($messagePayload['stream'])) {
            return null;
        }
        return (new BinancePriceMessageExtractor())($messagePayload['data'], $ticker, $interval);
    }
}
