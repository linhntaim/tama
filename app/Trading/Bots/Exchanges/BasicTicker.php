<?php

namespace App\Trading\Bots\Exchanges;

class BasicTicker extends Ticker
{
    public function __construct(string $ticker, string $baseSymbol, string $quoteSymbol)
    {
        parent::__construct([
            'ticker' => $ticker,
            'base_symbol' => $baseSymbol,
            'quote_symbol' => $quoteSymbol,
        ]);
    }

    public function getSymbol(): string
    {
        return $this->get('ticker');
    }

    public function getBaseSymbol(): string
    {
        return $this->get('base_symbol');
    }

    public function getQuoteSymbol(): string
    {
        return $this->get('quote_symbol');
    }
}
