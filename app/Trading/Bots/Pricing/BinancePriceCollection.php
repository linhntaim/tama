<?php

namespace App\Trading\Bots\Pricing;

use App\Trading\Bots\Exchanges\Binance;

class BinancePriceCollection extends PriceCollection
{
    public function __construct(string $ticker, Interval $interval, array $prices, array $times)
    {
        parent::__construct(Binance::NAME, $ticker, $interval, $prices, $times);
    }

    /**
     * @return float[]
     */
    protected function createPrices(): array
    {
        return array_map(static fn($item) => (float)$item[4], $this->items);
    }
}
