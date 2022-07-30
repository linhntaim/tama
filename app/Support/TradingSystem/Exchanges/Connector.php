<?php

namespace App\Support\TradingSystem\Exchanges;

use App\Support\TradingSystem\Prices\Prices;

abstract class Connector
{
    public abstract function getPrices(string $ticker, string $interval, int $limit = 1000): Prices;
}
