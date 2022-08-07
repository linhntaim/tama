<?php

namespace App\Trading\Exchanges;

use App\Trading\Prices\Prices;
use App\Trading\Trader;
use Illuminate\Support\Collection;

abstract class Connector
{
    public const NAME = '';

    public final function getName(): string
    {
        return static::NAME;
    }

    public function isTickerOk(string $ticker): bool
    {
        return false;
    }

    public function isIntervalOk(string $interval): bool
    {
        return in_array($interval, [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
            Trader::INTERVAL_15_MINUTES,
            Trader::INTERVAL_30_MINUTES,
            Trader::INTERVAL_1_HOUR,
            Trader::INTERVAL_2_HOURS,
            Trader::INTERVAL_4_HOURS,
            Trader::INTERVAL_6_HOURS,
            Trader::INTERVAL_8_HOURS,
            Trader::INTERVAL_12_HOURS,
            Trader::INTERVAL_1_DAY,
            Trader::INTERVAL_3_DAYS,
            Trader::INTERVAL_1_WEEK,
            Trader::INTERVAL_1_MONTH,
        ]);
    }

    public function availableTickers(string|array|null $pattern = null): Collection
    {
        return collect([]);
    }

    public abstract function getPrices(string $ticker, string $interval, int $limit = 1000): Prices;
}
