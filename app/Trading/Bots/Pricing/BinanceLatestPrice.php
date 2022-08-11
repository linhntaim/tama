<?php

namespace App\Trading\Bots\Pricing;

class BinanceLatestPrice extends LatestPrice
{
    public function __construct(string $ticker, Interval|string $interval, array $price = [])
    {
        parent::__construct('binance', $ticker, $interval, $price);
    }

    public function getTime(): int
    {
        return (int)($this->getPrice()[0] / 1000);
    }
}
