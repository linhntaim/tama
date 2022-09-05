<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\PriceCollection as BasePriceCollection;

class PriceCollection extends BasePriceCollection
{
    public function __construct(string $ticker, Interval $interval, array $prices, array $times)
    {
        parent::__construct(Binance::NAME, $ticker, $interval, $prices, $times);
    }

    protected function create(string $exchange, string $ticker, Interval $interval, array $prices, array $times): static
    {
        return new static($ticker, $interval, $prices, $times);
    }

    /**
     * @return float[]
     */
    protected function createPrices(): array
    {
        return array_map(static fn($item) => (float)$item[4], $this->items);
    }
}
