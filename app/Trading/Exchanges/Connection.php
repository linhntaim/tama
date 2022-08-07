<?php

namespace App\Trading\Exchanges;

class Connection
{
    public static function create(string $name): Connector
    {
        return match ($name) {
            default => new BinanceConnector()
        };
    }
}
