<?php

namespace App\Trading\Bots\Exchanges;

class FakeMarketOrder extends MarketOrder
{
    public function __construct(
        protected float $baseAmount,
        protected float $quoteAmount
    )
    {
        parent::__construct();
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
