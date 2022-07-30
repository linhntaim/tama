<?php

namespace App\Support\TradingSystem\Prices;

class BinanceCandles extends Candles
{
    protected function extractClosePrice($price): float
    {
        return (float)$price[4];
    }
}
