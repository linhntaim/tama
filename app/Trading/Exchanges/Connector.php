<?php

namespace App\Trading\Exchanges;

use App\Trading\Prices\Candles;
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

    public function isTickerValid(string $ticker): bool
    {
        return false;
    }

    public function isIntervalValid(string $interval): bool
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

    protected abstract function getPrices(string $ticker, string $interval): array;

    protected abstract function getLatestPricesFromCache(string $ticker, string $interval): ?array;

    protected function createPrices(array $prices, string $ticker, string $interval): Prices
    {
        return new Candles(
            $ticker,
            $interval,
            $prices
        );
    }

    public function getLatestPrices(string $ticker, string $interval): Prices
    {
        return $this->createPrices(
            $this->getLatestPricesFromCache($ticker, $interval) ?? $this->getPrices($ticker, $interval),
            $ticker,
            $interval
        );
    }
}
