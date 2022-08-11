<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use App\Trading\Bots\Pricing\BinanceLatestPrice;
use App\Trading\Bots\Pricing\LatestPrice;

class BinancePriceMessageExtractor implements IPriceMessageExtract
{
    public function __invoke(array $messagePayload): ?LatestPrice
    {
        return $this->handlePricePayload($messagePayload['k']);
    }

    protected function handlePricePayload(array $pricePayload): ?LatestPrice
    {
        return $pricePayload['x'] // Closed?
            ? new BinanceLatestPrice(
                $pricePayload['s'],
                $pricePayload['i'],
                [
                    $pricePayload['t'], // Open time
                    $pricePayload['o'], // Open
                    $pricePayload['h'], // High
                    $pricePayload['l'], // Low
                    $pricePayload['c'], // Close
                    $pricePayload['v'], // Volume
                    $pricePayload['T'], // Close time
                    $pricePayload['q'], // Quote asset volume
                    $pricePayload['n'], // Number of trades
                    $pricePayload['V'], // Taker buy base asset volume
                    $pricePayload['Q'], // Taker buy quote asset volume
                    $pricePayload['B'], // Ignore
                ]
            )
            : null;
    }
}
