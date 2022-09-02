<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;

class Trade extends ArrayReader
{
    public function getBaseAmount(): float
    {
        return $this->get('base_amount', 0.0);
    }

    public function getQuoteAmount(): float
    {
        return $this->get('quote_amount', 0.0);
    }
}
