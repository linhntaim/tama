<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\Price as BasePrice;

/**
 * @see https://binance-docs.github.io/apidocs/spot/en/#kline-candlestick-data
 */
class Price extends BasePrice
{
    public function setTime(int $openTime, Interval $interval): static
    {
        $this->data[0] = $openTime * 1000;
        $this->data[6] = $interval->getNextOpenTimeOfExact($openTime) * 1000 - 1;
        return $this;
    }

    public function getOpenTime(): int
    {
        return int_floor($this->data[0] / 1000);
    }

    public function getClosePrice(): string
    {
        return $this->data[4];
    }
}
