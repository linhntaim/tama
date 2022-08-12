<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use InvalidArgumentException;

class Factory
{
    public static function create(string $exchange): PriceStream
    {
        return match ($exchange) {
            'binance' => new BinancePriceStream(),
            default => throw new InvalidArgumentException(sprintf('Price stream for the exchange "%s" does not exist.', $exchange))
        };
    }
}
