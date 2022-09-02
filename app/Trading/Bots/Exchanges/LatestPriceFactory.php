<?php

namespace App\Trading\Bots\Exchanges;

use InvalidArgumentException;

class LatestPriceFactory
{
    public static function create(string $exchange, string $ticker, Interval|string $interval, array $price): LatestPrice
    {
        return match ($exchange) {
            Binance::NAME => new BinanceLatestPrice($ticker, $interval, $price),
            default => throw new InvalidArgumentException(sprintf('Latest price for the exchange "%s" does not exists.', $exchange))
        };
    }
}
