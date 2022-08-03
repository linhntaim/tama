<?php

namespace App\Trading\Exchanges;

use App\Trading\Prices\Prices;

abstract class Connector
{
    public abstract function getPrices(string $ticker, string $interval, int $limit = 1000): Prices;
}
