<?php

namespace App\Trading\Bots\Exchanges;

class Factory
{
    protected static array $disables;

    protected static array $enables;

    public static function availables(): array
    {
        return [
            Binance::NAME,
        ];
    }

    public static function disables(): array
    {
        return static::$disables ?? static::$disables = trading_cfg_exchange_disables();
    }

    public static function enables(): array
    {
        return static::$enables ?? static::$enables = array_diff(static::availables(), static::disables());
    }

    public static function enabled(string $exchange): bool
    {
        return in_array($exchange, static::enables(), true);
    }
}
