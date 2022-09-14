<?php

namespace App\Trading\Bots\Exchanges;

use App\Trading\Bots\Exchanges\Binance\Binance;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Manager;
use React\EventLoop\LoopInterface;

class ExchangeManager extends Manager
{
    protected array $builtinExchanges = [
        Binance::NAME,
    ];

    protected array $availableExchanges;

    public function getAvailableExchanges(): array
    {
        return $this->availableExchanges ?? $this->availableExchanges = array_diff($this->enables(), $this->disables());
    }

    protected function disables(): array
    {
        return trading_cfg_exchange_disables();
    }

    protected function enables(): array
    {
        return array_merge($this->builtinExchanges, array_keys($this->customCreators));
    }

    public function available(?string $exchange = null): array|bool
    {
        $available = $this->getAvailableExchanges();
        return is_null($exchange) ? $available : in_array($exchange, $available, true);
    }

    public function getDefaultDriver(): string
    {
        return Binance::NAME;
    }

    public function exchange(?string $exchange = null): Exchange
    {
        return $this->driver($exchange);
    }

    public function connector(?string $exchange = null, array $options = [], CacheRepository|string|null $cache = 'redis'): ConnectorInterface
    {
        return $this->exchange($exchange)->createConnector($options, $cache);
    }

    public function priceStream(LoopInterface $loop, ?string $exchange = null): PriceStream
    {
        return $this->exchange($exchange)->createPriceStream($loop);
    }

    protected function createBinanceDriver(): Binance
    {
        return new Binance();
    }
}
