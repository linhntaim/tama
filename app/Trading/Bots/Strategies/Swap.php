<?php

namespace App\Trading\Bots\Strategies;

class Swap
{
    public function __construct(
        protected float $baseAmount,
        protected float $quoteAmount,
    )
    {
    }

    public function getBaseAmount(): float
    {
        return $this->baseAmount;
    }

    public function getQuoteAmount(): float
    {
        return $this->quoteAmount;
    }
}
