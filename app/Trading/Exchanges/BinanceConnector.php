<?php

namespace App\Trading\Exchanges;

use App\Trading\Prices\BinanceCandles;
use App\Trading\Prices\Prices;
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
        $ticker = strtoupper($ticker);
        return new BinanceCandles(
            $ticker,
            $interval,
            $this->spot->klines($ticker, $interval, [
                'limit' => $limit,
            ])
        );
    }
}
