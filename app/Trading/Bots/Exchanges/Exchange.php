<?php

namespace App\Trading\Bots\Exchanges;

use App\Trading\Trader;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use React\EventLoop\LoopInterface;

abstract class Exchange
{
    public const NAME = '';
    public const PRICE_LIMIT = 1000;
    public const DEFAULT_INTERVAL = Trader::INTERVAL_1_DAY;

    abstract public function createConnector(array $options = [], CacheRepository|string|null $cache = 'redis'): Connector;

    abstract public function createPriceStream(LoopInterface $loop): PriceStream;
}
