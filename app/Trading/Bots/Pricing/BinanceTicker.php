<?php

namespace App\Trading\Bots\Pricing;

class BinanceTicker extends Ticker
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
