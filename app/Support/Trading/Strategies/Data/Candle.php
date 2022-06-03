<?php

namespace App\Support\Trading\Strategies\Data;

class Candle extends DataItem
{
    protected float $closeValue;

    public function getValue(): float
    {
        return $this->closeValue;
    }
}
