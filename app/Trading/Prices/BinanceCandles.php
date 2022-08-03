<?php

namespace App\Trading\Prices;

class BinanceCandles extends Candles
{
    protected function extractCloseValue($price): float
    {
        return (float)$price[4];
    }
}
