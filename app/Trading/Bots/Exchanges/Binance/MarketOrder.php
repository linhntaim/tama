<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\MarketOrder as BaseMarketOrder;

class MarketOrder extends BaseMarketOrder
{
    public function buy(): bool
    {
        return $this->get('side') === 'BUY';
    }

    public function sell(): bool
    {
        return $this->get('side') === 'SELL';
    }

    public function getTime(): int
    {
        return int_floor($this->get('transactTime') / 1000);
    }

    public function getPrice(): string
    {
        return $this->get('price');
    }

    public function getFromAmount(): string
    {
        return $this->get('executedQty');
    }

    public function getToAmount(): string
    {
        return $this->get('cummulativeQuoteQty');
    }
}
