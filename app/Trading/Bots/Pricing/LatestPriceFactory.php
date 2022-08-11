<?php

namespace App\Trading\Bots\Pricing;

use InvalidArgumentException;

class LatestPriceFactory
{
    public static function create(string $exchange, string $ticker, Interval|string $interval, array $price): LatestPrice
    {
        return match ($exchange) {
            'binance' => new BinanceLatestPrice($ticker, $interval, $price),
            default => throw new InvalidArgumentException(sprintf('Latest price for the exchange "%s" does not exists.', $exchange))
        };
    }
}
