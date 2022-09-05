<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Binance\LatestPrice as BinanceLatestPrice;
use App\Trading\Bots\Exchanges\PriceMessageExtract;
use App\Trading\Bots\Exchanges\LatestPrice;

class PriceMessageExtractor implements PriceMessageExtract
{
    public function __invoke(array $messagePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice
    {
        if (!isset($messagePayload['e']) || $messagePayload['e'] !== 'kline') {
            return null;
        }
        return $this->handlePricePayload($messagePayload['k'], $ticker, $interval);
    }

    protected function handlePricePayload(array $pricePayload, ?string &$ticker = null, ?string &$interval = null): ?LatestPrice
    {
        $ticker = $pricePayload['s'];
        $interval = $pricePayload['i'];
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
