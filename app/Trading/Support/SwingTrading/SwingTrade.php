<?php

namespace App\Trading\Support\SwingTrading;

use App\Trading\Support\PricedValue;

class SwingTrade extends PricedValue
{
    protected bool $buy = true;

    protected float $strength;

    public function __construct(?string $time, ?float $price, float $strength)
    {
        parent::__construct($time, $price);

        $this->strength = $strength;
    }

    public function sell(): static
    {
        $this->buy = false;
        return $this;
    }

    public function toArray(): array
    {
        return parent::toArray()
            + [
                $this->buy ? 'buy' : 'sell' => true,
                'strength' => $this->strength,
            ];
    }
}
