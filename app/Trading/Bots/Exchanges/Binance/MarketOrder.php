<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\MarketOrder as BaseMarketOrder;

class MarketOrder extends BaseMarketOrder
{
    public function getBaseAmount(): float
    {
        return $this->get('executedQty');
    }

    public function getQuoteAmount(): float
    {
        return $this->get('cummulativeQuoteQty');
    }
}
