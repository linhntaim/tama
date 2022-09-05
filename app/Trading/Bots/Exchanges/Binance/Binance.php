<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Connector as BaseConnector;
use App\Trading\Bots\Exchanges\Exchange;
use App\Trading\Bots\Exchanges\PriceStream as BasePriceStream;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use React\EventLoop\LoopInterface;

class Binance extends Exchange
{
    public const NAME = 'binance';
    public const DEFAULT_TICKER = 'BTCUSDT';

    public function createConnector(array $options = [], CacheRepository|string|null $cache = 'redis'): BaseConnector
    {
        return new Connector($options, $cache);
    }

    public function createPriceStream(LoopInterface $loop): BasePriceStream
    {
        return new PriceStream($loop);
    }
}
