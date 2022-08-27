<?php

namespace App\Trading\Support;

class Peak
{
    public int|float $value;

    public function __construct(int|float $value)
    {
        $this->value = $value;
    }
}
