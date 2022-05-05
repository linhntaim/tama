<?php

namespace App\Support\Trading;

use App\Support\Trader;

abstract class SwingTradeIndicator
{
    protected Trader $trader;

    public function __construct()
    {
        $this->trader = new Trader();
    }

    public abstract function guessBuying(TradingData $data): ?array;
}