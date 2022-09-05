<?php

namespace App\Trading\Bots\Exchanges;

abstract class MarketOrder extends Order
{
    abstract public function getBaseAmount(): float;

    abstract public function getQuoteAmount(): float;
}
