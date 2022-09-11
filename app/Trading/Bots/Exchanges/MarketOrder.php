<?php

namespace App\Trading\Bots\Exchanges;

abstract class MarketOrder extends Order
{
    abstract public function sell(): bool;

    abstract public function buy(): bool;

    abstract public function getTime(): int;

    abstract public function getPrice(): string;

    abstract public function getFromAmount(): string;

    abstract public function getToAmount(): string;
}
