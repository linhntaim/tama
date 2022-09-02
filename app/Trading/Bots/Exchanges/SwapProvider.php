<?php

namespace App\Trading\Bots\Exchanges;

abstract class SwapProvider
{
    abstract public function swap(string $fromSymbol, string $toSymbol, float $fromAmount): array;
}
