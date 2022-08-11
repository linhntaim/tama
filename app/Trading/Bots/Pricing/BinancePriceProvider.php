<?php

namespace App\Trading\Bots\Pricing;

use Binance\Exception\MissingArgumentException;
use Binance\Spot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class BinancePriceProvider extends PriceProvider
{
    public function __construct(string $ticker, Interval $interval, CacheRepository|string|null $cache = 'redis')
    {
        parent::__construct('binance', $ticker, $interval, $cache);
    }

    /**
     * @throws MissingArgumentException
     */
    public function fetch(int $startTime = null, int $endTime = null, int $limit = 1000): array
    {
        return (new Spot())->klines($this->ticker, $this->interval, array_filter([
            'startTime' => $startTime,
            'endTime' => $endTime,
            'limit' => $limit,
        ]));
    }
}
