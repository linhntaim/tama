<?php

namespace App\Trading\Bots\Exchanges;

use InvalidArgumentException;

class SwapperFactory
{
    public static function create(string $exchange): SwapProvider
    {
        return match ($exchange) {
            Binance::NAME => new BinanceSwapProvider(),
            default => throw new InvalidArgumentException(sprintf('SwapProvider for the exchange "%s" does not exist.', $exchange))
        };
    }
}
