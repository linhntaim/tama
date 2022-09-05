<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Connector as BaseConnector;
use App\Trading\Bots\Exchanges\Exchange;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\MarketOrder as BaseMarketOrder;
use App\Trading\Bots\Exchanges\Ticker as BaseTicker;
use App\Trading\Models\UserExchangeOption;
use Binance\Exception\MissingArgumentException;
use Binance\Spot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class Connector extends BaseConnector
{
    protected const ORDER_SIDE_BUY = 'BUY';
    protected const ORDER_SIDE_SELL = 'SELL';
    protected const ORDER_TYPE_MARKET = 'MARKET';

    protected Spot $spot;

    public function __construct(array $options = [], CacheRepository|string|null $cache = 'redis')
    {
        parent::__construct(Binance::NAME, $options, $cache);

        $this->spot = new Spot(Arr::only($this->options, [
            'baseURL',
            'key',
            'secret',
            'logger',
            'timeout',
            'showWeightUsage',
            'showHeader',
            'httpClient',
        ]));
    }

    protected function withUserExchangeOption(UserExchangeOption $option): static
    {
        return new Connector(
            array_merge(
                $this->options,
                [
                    'key' => $option->api_key,
                    'secret' => $option->api_secret,
                ]
            ),
            $this->cacheStore
        );
    }

    public function isTickerValid(string $ticker): false|BaseTicker
    {
        try {
            $ticker = $this->spot->exchangeInfo([
                'symbol' => $ticker,
            ])['symbols'][0];

            return $ticker['status'] === 'TRADING' && in_array('SPOT', $ticker['permissions'], true)
                ? new Ticker($ticker) : false;
        }
        catch (Throwable) {
            return false;
        }
    }

    public function availableTickers(string|array|null $pattern = null): Collection
    {
        return collect($this->spot->exchangeInfo()['symbols'])
            ->filter(function ($ticker) use ($pattern) {
                return $ticker['status'] === 'TRADING'
                    && in_array('SPOT', $ticker['permissions'], true)
                    && (is_null($pattern) || Str::is($pattern, $ticker['symbol']));
            })
            ->map(function (array $ticker) {
                return new Ticker($ticker);
            });
    }

    /**
     * @throws MissingArgumentException
     */
    public function fetchPrices(string $ticker, Interval $interval, int $startTime = null, int $endTime = null, int $limit = Exchange::PRICE_LIMIT): array
    {
        return $this->spot->klines($ticker, (string)$interval, array_filter([
            'startTime' => is_null($startTime) ? null : $startTime * 1000, // ms
            'endTime' => is_null($endTime) ? null : $endTime * 1000, // ms
            'limit' => $limit,
        ]));
    }

    protected function priceCollectionClass(): string
    {
        return PriceCollection::class;
    }

    /**
     * @throws MissingArgumentException
     */
    protected function createMarketOrder(string $ticker, float $amount, string $side): BaseMarketOrder
    {
        return new MarketOrder(
            $this->spot->newOrder($ticker, $side, static::ORDER_TYPE_MARKET, [
                'quantity' => $amount,
            ])
        );
    }
}
