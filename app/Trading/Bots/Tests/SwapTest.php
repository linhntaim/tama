<?php

namespace App\Trading\Bots\Tests;

use App\Support\ArrayReader;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\MarketOrder;

class SwapTest extends ArrayReader
{
    public function __construct(?Indication $indication, int $time, string $price, string $baseAmount, string $quoteAmount, ?MarketOrder $exchangeOrder)
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

    public function getPrice(): string
    {
        return $this->get('price');
    }

    public function getBaseAmount(): string
    {
        return $this->get('base_amount');
    }

    public function getQuoteAmount(): string
    {
        return $this->get('quote_amount');
    }

    public function baseSwapped(): bool
    {
        return num_lt($this->getBaseAmount(), 0);
    }

    public function quoteSwapped(): bool
    {
        return num_lt($this->getQuoteAmount(), 0);
    }
}
