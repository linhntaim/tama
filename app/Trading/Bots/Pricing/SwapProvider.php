<?php

namespace App\Trading\Bots\Pricing;

abstract class SwapProvider
{
    abstract public function buy(string $ticker, float $amount): array;

    abstract public function sell(string $ticker, float $amount): array;
}
