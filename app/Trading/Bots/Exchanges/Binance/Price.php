<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Price as BasePrice;

/**
 * @see https://binance-docs.github.io/apidocs/spot/en/#kline-candlestick-data
 */
class Price extends BasePrice
{
    public function getOpenTime(): int
    {
        return int_floor($this->data[0] / 1000);
    }

    public function getClosePrice(): string
    {
        return $this->data[4];
    }
}
