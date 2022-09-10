<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\LatestPrice as BaseLatestPrice;

class LatestPrice extends BaseLatestPrice
{
    public function __construct(string $ticker, Interval|string $interval, array $price = [])
    {
        parent::__construct(Binance::NAME, $ticker, $interval, $price);
    }

    public function getTime(): int
    {
        return int_floor($this->getPrice()[0] / 1000);
    }
}
