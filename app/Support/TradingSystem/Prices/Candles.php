<?php

namespace App\Support\TradingSystem\Prices;

use InvalidArgumentException;

class Candles extends Prices
{
    /**
     * @return array
     */
    public function getPrices(): array
    {
        return array_map(fn($item) => $this->extractClosePrice($item), $this->data);
    }

    protected function extractClosePrice($price): float
    {
        return $price['close'] ?? throw new InvalidArgumentException('Price has no close field.');
    }
}
