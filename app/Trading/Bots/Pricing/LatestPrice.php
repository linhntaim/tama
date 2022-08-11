<?php

namespace App\Trading\Bots\Pricing;

use App\Support\ArrayReader;

abstract class LatestPrice extends ArrayReader
{
    public function __construct(string $exchange, string $ticker, Interval|string $interval, array $price = [])
    {
        parent::__construct([
            'exchange' => $exchange,
            'ticker' => $ticker,
            'interval' => $interval instanceof Interval ? $interval : new Interval($interval),
            'price' => $price,
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

    public function getPrice(): array
    {
        return $this->get('price');
    }

    public abstract function getTime(): int; // timestamp seconds
}
