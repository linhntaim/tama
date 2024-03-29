<?php

namespace App\Trading\Bots\Exchanges\Binance;

use App\Trading\Bots\Exchanges\Connector as BaseConnector;
use App\Trading\Bots\Exchanges\Exchange;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\MarketOrder as BaseMarketOrder;
use App\Trading\Bots\Exchanges\PriceCollection as BasePriceCollection;
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

    public function availableTickers(
        string|array|null $quoteSymbol = null,
        string|array|null $baseSymbol = null,
        string|array|null $exceptQuoteSymbol = null,
        string|array|null $exceptBaseSymbol = null
    ): Collection
    {
        return collect($this->spot->exchangeInfo()['symbols'])
            ->filter(function ($ticker) use ($quoteSymbol, $baseSymbol, $exceptQuoteSymbol, $exceptBaseSymbol) {
                return $ticker['status'] === 'TRADING'
                    && in_array('SPOT', $ticker['permissions'], true)
                    && (is_null($quoteSymbol) || in_array($ticker['quoteAsset'], (array)$quoteSymbol, true))
                    && (is_null($baseSymbol) || in_array($ticker['baseAsset'], (array)$baseSymbol, true))
                    && (is_null($exceptQuoteSymbol) || !in_array($ticker['quoteAsset'], (array)$exceptQuoteSymbol, true))
                    && (is_null($exceptBaseSymbol) || !in_array($ticker['baseAsset'], (array)$exceptBaseSymbol, true));
            })
            ->map(function (array $ticker) {
                return new Ticker($ticker);
            });
    }

    public function tickerPrice(string $ticker): string
    {
        return $this->spot->tickerPrice(['symbol' => $ticker])['price'];
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

    protected function newPriceCollection(string $ticker, Interval $interval, array $prices, array $times): BasePriceCollection
    {
        return new PriceCollection($ticker, $interval, $prices, $times);
    }

    /**
     * @throws MissingArgumentException
     */
    protected function createMarketOrder(string $ticker, string $amount, string $side): BaseMarketOrder
    {
        return new MarketOrder(
            $this->spot->newOrder($ticker, $side, static::ORDER_TYPE_MARKET, [
                'quantity' => $amount,
            ])
        );
    }
}
