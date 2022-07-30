<?php

namespace App\Support\TradingSystem\Analyzer;

abstract class Signal
{
    public function __construct(
        public float $value
    )
    {
    }
}
