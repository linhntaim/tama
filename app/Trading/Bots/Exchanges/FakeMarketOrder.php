<?php

namespace App\Trading\Bots\Exchanges;

class FakeMarketOrder extends MarketOrder
{
    public function __construct(float $fromAmount, float $toAmount)
    {
        parent::__construct([
            'from_amount' => $fromAmount,
            'to_amount' => $toAmount,
        ]);
    }

    public function getFromAmount(): float
    {
        return $this->get('from_amount');
    }

    public function getToAmount(): float
    {
        return $this->get('to_amount');
    }
}
