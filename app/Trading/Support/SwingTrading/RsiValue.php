<?php

namespace App\Trading\Support\SwingTrading;

use App\Trading\Support\PricedValue;

class RsiValue extends PricedValue
{
    protected float $rsi;

    public function __construct(string $time, float $price, float $rsi)
    {
        parent::__construct($time, $price);

        $this->rsi = $rsi;
    }

    public function toArray(): array
    {
        return parent::toArray() +
            [
                'rsi' => $this->rsi,
            ];
    }
}
