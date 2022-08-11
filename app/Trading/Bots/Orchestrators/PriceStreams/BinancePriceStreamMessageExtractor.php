<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Pricing\LatestPrice;

class BinancePriceStreamMessageExtractor implements IPriceMessageExtract
{
    public function __invoke(array $messagePayload): ?LatestPrice
    {
        return (new BinancePriceMessageExtractor())($messagePayload['data']);
    }
}
