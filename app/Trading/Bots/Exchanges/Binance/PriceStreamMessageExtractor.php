<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\PriceMessageExtract;
use App\Trading\Bots\Exchanges\LatestPrice;

class PriceStreamMessageExtractor implements PriceMessageExtract
{
    public function __invoke(array $messagePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice
    {
        $ticker = $interval = null;
        if (!isset($messagePayload['stream'])) {
            return null;
        }
        return (new PriceMessageExtractor())($messagePayload['data'], $ticker, $interval);
    }
}
