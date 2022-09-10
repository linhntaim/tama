<?php

namespace App\Trading\Bots\Tests;

use App\Support\ArrayReader;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\MarketOrder;

class SwapTest extends ArrayReader
{
    public function __construct(?Indication $indication, int $time, float $price, float $baseAmount, float $quoteAmount, ?MarketOrder $exchangeOrder)
    {
        parent::__construct([
            'indication' => $indication,
            'time' => $time,
            'price' => $price,
            'base_amount' => num_floor($baseAmount),
            'quote_amount' => num_floor($quoteAmount),
            'exchange_order' => $exchangeOrder,
        ]);
    }

    public function getTime(): int
    {
        return $this->get('time');
    }

    public function getPrice(): float
    {
        return $this->get('price');
    }

    public function getBaseAmount(): float
    {
        return $this->get('base_amount');
    }

    public function getQuoteAmount(): float
    {
        return $this->get('quote_amount');
    }

    public function baseSwapped(): bool
    {
        return $this->getBaseAmount() < 0;
    }

    public function quoteSwapped(): bool
    {
        return $this->getQuoteAmount() < 0;
    }
}
