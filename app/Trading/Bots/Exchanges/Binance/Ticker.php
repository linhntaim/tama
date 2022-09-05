<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Ticker as BaseTicker;

class Ticker extends BaseTicker
{
    public function getSymbol(): string
    {
        return $this->get('symbol');
    }

    public function getBaseSymbol(): string
    {
        return $this->get('baseAsset');
    }

    public function getQuoteSymbol(): string
    {
        return $this->get('quoteAsset');
    }
}
