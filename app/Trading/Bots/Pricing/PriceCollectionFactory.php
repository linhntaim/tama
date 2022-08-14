<?php

namespace App\Trading\Bots\Pricing;

use App\Trading\Bots\Exchanges\Binance;
use InvalidArgumentException;

class PriceCollectionFactory
{
    public static function create(
        string   $exchange,
        string   $ticker,
        Interval $interval,
        array    $prices,
        array    $times
    ): PriceCollection
    {
        return match ($exchange) {
            Binance::NAME => new BinancePriceCollection($ticker, $interval, $prices, $times),
            default => throw new InvalidArgumentException(sprintf('Price collection for the exchange "%s" does not exist', $exchange))
        };
    }
}
