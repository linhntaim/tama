<?php

namespace App\Support\Trading;

class Peak
{
    public int|float $value;

    public function __construct(int|float $value)
    {
        $this->value = $value;
    }
}