<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\MarketOrder as BaseMarketOrder;

class MarketOrder extends BaseMarketOrder
{
    public function getFromAmount(): float
    {
        return $this->get('executedQty');
    }

    public function getToAmount(): float
    {
        return $this->get('cummulativeQuoteQty');
    }
}
