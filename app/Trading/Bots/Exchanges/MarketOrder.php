<?php

namespace App\Trading\Bots\Exchanges;

abstract class MarketOrder extends Order
{
    abstract public function sell(): bool;

    abstract public function buy(): bool;

    abstract public function getTime(): int;

    abstract public function getPrice(): float;

    abstract public function getFromAmount(): float;

    abstract public function getToAmount(): float;
}
