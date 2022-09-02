<?php

namespace App\Trading\Bots\Pricing;

abstract class SwapProvider
{
    abstract public function swap(string $fromSymbol, string $toSymbol, float $fromAmount): array;
}
