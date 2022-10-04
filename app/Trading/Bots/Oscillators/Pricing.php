<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Exchanges\PriceCollection;

trait Pricing
{
    protected function getPrices(Packet $packet): PriceCollection
    {
        return $packet->get('inputs.prices');
    }
}