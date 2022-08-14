<?php

namespace App\Trading\Bots\Pricing;

use App\Trading\Bots\Exchanges\Binance;

class BinanceLatestPrice extends LatestPrice
{
    public function __construct(string $ticker, Interval|string $interval, array $actionPrice = [])
    {
        parent::__construct(Binance::NAME, $ticker, $interval, $actionPrice);
    }

    public function getTime(): int
    {
        return (int)($this->getPrice()[0] / 1000);
    }
}
