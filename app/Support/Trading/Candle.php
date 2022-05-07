<?php

namespace App\Support\Trading;

class Candle
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getClose(): float
    {
        return (float)$this->data['close'] ?? 0;
    }
}