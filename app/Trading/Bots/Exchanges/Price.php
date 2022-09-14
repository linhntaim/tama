<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;

abstract class Price extends ArrayReader
{
    abstract public function getOpenTime(): int;

    abstract public function getClosePrice(): string;

    public function getPrice(): string
    {
        return $this->getClosePrice();
    }
}
