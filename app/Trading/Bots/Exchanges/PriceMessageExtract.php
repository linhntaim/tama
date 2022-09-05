<?php

namespace App\Trading\Bots\Exchanges;

interface PriceMessageExtract
{
    public function __invoke(array $messagePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice;
}
