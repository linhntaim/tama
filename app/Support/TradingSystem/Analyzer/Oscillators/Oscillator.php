<?php

namespace App\Support\TradingSystem\Analyzer\Oscillators;

use App\Support\TradingSystem\Prices\Prices;

abstract class Oscillator
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    public abstract function signal(Prices $prices, ?array &$history = null): float;
}
