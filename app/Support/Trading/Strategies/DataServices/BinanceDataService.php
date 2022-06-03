<?php

namespace App\Support\Trading\Strategies\DataServices;

use App\Support\Trading\Strategies\Data\Data;

class BinanceDataService extends DataService
{
    public const NAME = 'binance';

    public function getPrices(string $baseSymbol, string $quoteSymbol, string $interval, int $limit = 1000): Data
    {
        // TODO: Implement getPrices() method.
    }

    public function getAmount(string $symbol): float
    {
        // TODO: Implement getAmount() method.
    }

    public function buy(string $symbol, float $amount)
    {
        // TODO: Implement buy() method.
    }

    public function sell(string $symbol, float $amount)
    {
        // TODO: Implement sell() method.
    }
}
