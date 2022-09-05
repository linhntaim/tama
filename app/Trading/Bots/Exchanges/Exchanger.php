<?php

namespace App\Trading\Bots\Exchanges;

use Closure;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Facade;
use React\EventLoop\LoopInterface;

/**
 * @method static ExchangeManager extend(string $driver, Closure $callback)
 * @method static array|bool available(?string $exchange = null)
 * @method static Exchange exchange(?string $exchange = null)
 * @method static ConnectorInterface connector(?string $exchange = null, array $options = [], CacheRepository|string|null $cache = 'redis')
 * @method static PriceStream priceStream(LoopInterface $loop, ?string $exchange = null)
 *
 * @see ExchangeManager
 */
class Exchanger extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ExchangeManager::class;
    }
}
