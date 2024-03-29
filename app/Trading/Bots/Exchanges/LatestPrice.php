<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;

abstract class LatestPrice extends ArrayReader
{
    public function __construct(string $exchange, string $ticker, Interval|string $interval, array $price = [])
    {
        parent::__construct([
            'exchange' => $exchange,
            'ticker' => $ticker,
            'interval' => $interval instanceof Interval ? $interval : new Interval($interval),
            'price' => Exchanger::exchange($exchange)->createPrice($price),
        ]);
    }

    public function getExchange(): string
    {
        return $this->get('exchange');
    }

    public function getTicker(): string
    {
        return $this->get('ticker');
    }

    public function getInterval(): Interval
    {
        return $this->get('interval');
    }

    public function getPrice(): Price
    {
        return $this->get('price');
    }
}
