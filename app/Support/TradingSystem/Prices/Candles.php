<?php

namespace App\Support\TradingSystem\Prices;

use InvalidArgumentException;

class Candles extends Prices
{
    protected array $closeData;

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->closeData ?? $this->closeData = array_map(fn($item) => $this->extractCloseValue($item), $this->data);
    }

    protected function extractCloseValue($price): float
    {
        return $price['close'] ?? throw new InvalidArgumentException('Price has no close field.');
    }
}
