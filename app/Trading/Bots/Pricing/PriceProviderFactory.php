<?php

namespace App\Trading\Bots\Pricing;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use InvalidArgumentException;

class PriceProviderFactory
{
    public static function create(
        string                      $exchange,
        string                      $ticker,
        Interval                    $interval,
        CacheRepository|string|null $cache = 'redis'
    ): PriceProvider
    {
        return match ($exchange) {
            'binance' => new BinancePriceProvider($ticker, $interval, $cache),
            default => throw new InvalidArgumentException(sprintf('Price provider for the exchange "%s" does not exists.', $exchange))
        };
    }
}
