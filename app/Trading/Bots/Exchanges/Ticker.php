<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;
use Stringable;

abstract class Ticker extends ArrayReader implements Stringable
{
    abstract public function getSymbol(): string;

    abstract public function getBaseSymbol(): string;

    abstract public function getQuoteSymbol(): string;

    public function __toString(): string
    {
        return $this->getSymbol();
    }
}
