<?php

namespace App\Trading\Bots\Orchestrators\PriceStreams;

class Factory
{
    public static function create(string $exchange)
    {
        return match ($exchange) {
            default => new BinancePriceStream($exchange)
        };
    }
}
