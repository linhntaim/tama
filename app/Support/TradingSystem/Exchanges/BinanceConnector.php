<?php

namespace App\Support\TradingSystem\Exchanges;

use App\Support\TradingSystem\Prices\BinanceCandles;
use App\Support\TradingSystem\Prices\Prices;
use Binance\Spot as BinanceSpot;

class BinanceConnector extends Connector
{
    protected BinanceSpot $spot;

    public function __construct()
    {
        $this->spot = new BinanceSpot();
    }

    public function getPrices(string $ticker, string $interval, int $limit = 1000): Prices
    {
        return new BinanceCandles($this->spot->klines($ticker, $interval, [
            'limit' => $limit,
        ]), $interval);
    }
}
