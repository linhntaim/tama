<?php

namespace App\Trading\Bots\Exchanges;

abstract class MarketOrder extends Order
{
    abstract public function getFromAmount(): float;

    abstract public function getToAmount(): float;
}
