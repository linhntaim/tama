<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Pricing\LatestPrice;

class BinancePriceStreamMessageExtractor implements IPriceMessageExtract
{
    public function __invoke(array $messagePayload): ?LatestPrice
    {
        if (!isset($messagePayload['stream'])) {
            return null;
        }
        return (new BinancePriceMessageExtractor())($messagePayload['data']);
    }
}