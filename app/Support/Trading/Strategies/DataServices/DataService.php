<?php

namespace App\Support\Trading\Strategies\DataServices;

use App\Support\Trading\Strategies\Data\Data;

abstract class DataService
{
    public const NAME = 'default';

    public abstract function getPrices(string $baseSymbol, string $quoteSymbol, string $interval, int $limit = 1000): Data;

    public abstract function getAmount(string $symbol): float;

    public abstract function buy(string $symbol, float $amount);

    public abstract function sell(string $symbol, float $amount);
}
