<?php

namespace App\Support\Trading\Strategies\Data;

class Point extends DataItem
{
    protected float $value;

    public function getValue(): float
    {
        return $this->value;
    }
}
