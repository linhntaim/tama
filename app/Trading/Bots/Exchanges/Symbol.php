<?php

namespace App\Trading\Bots\Exchanges;

use App\Support\ArrayReader;

class Symbol extends ArrayReader
{
    public function __construct(string $price, string $tradeUrl)
    {
        parent::__construct([
            'price' => $price,
            'trade_url' => $tradeUrl,
        ]);
    }
}