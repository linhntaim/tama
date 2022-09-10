<?php

namespace App\Trading\Bots\Exchanges;

class FakeMarketOrder extends MarketOrder
{
    public function __construct(float $price, float $fromAmount, float $toAmount, bool $buy = true)
    {
        parent::__construct([
            'time' => time(),
            'price' => $price,
            'from_amount' => $fromAmount,
            'to_amount' => $toAmount,
            'buy' => $buy,
        ]);
    }

    public function buy(): bool
    {
        return $this->get('buy');
    }

    public function sell(): bool
    {
        return !$this->buy();
    }

    public function getTime(): int
    {
        return $this->get('time');
    }

    public function getPrice(): float
    {
        return $this->get('price');
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
