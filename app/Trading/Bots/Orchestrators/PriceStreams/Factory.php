<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

use InvalidArgumentException;
use React\EventLoop\LoopInterface;

class Factory
{
    public static function create(LoopInterface $loop, string $exchange): PriceStream
    {
        return match ($exchange) {
            'binance' => new BinancePriceStream($loop),
            default => throw new InvalidArgumentException(sprintf('Price stream for the exchange "%s" does not exist.', $exchange))
        };
    }
}
