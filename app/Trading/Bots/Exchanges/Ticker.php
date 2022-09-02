<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;

abstract class Ticker extends ArrayReader
{
    abstract public function getSymbol(): string;

    abstract public function getBaseSymbol(): string;

    abstract public function getQuoteSymbol(): string;
}
