<?php

namespace App\Trading\Bots\Exchanges;

use App\Models\User;
use App\Trading\Models\UserExchangeOption;
use App\Trading\Trader;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;
use RuntimeException;
use Throwable;

abstract class Connector implements ConnectorInterface
{
    protected const ORDER_SIDE_BUY = 'buy';
    protected const ORDER_SIDE_SELL = 'sell';

    protected CacheRepository $cacheStore;

    public function __construct(
        protected string            $exchange,
        protected array             $options = [],
        CacheRepository|string|null $cache = 'redis'
    )
    {
        $this->cacheStore = $cache instanceof CacheRepository ? $cache : Cache::store($cache);
    }

    abstract protected function withUserExchangeOption(UserExchangeOption $option): static;

    public function withUser(User $user): static
    {
        $option = $user->exchangeOption($this->exchange);
        if (is_null($option)) {
            throw new RuntimeException('Exchange option is not provided.');
        }
        return $this->withUserExchangeOption($option);
    }

    public function isTickerValid(string $ticker): false|Ticker
    {
        return false;
    }

    public function intervals(): array
    {
        return [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
            Trader::INTERVAL_15_MINUTES,
            Trader::INTERVAL_30_MINUTES,
            Trader::INTERVAL_1_HOUR,
            Trader::INTERVAL_2_HOURS,
            Trader::INTERVAL_4_HOURS,
            Trader::INTERVAL_6_HOURS,
            Trader::INTERVAL_8_HOURS,
            Trader::INTERVAL_12_HOURS,
            Trader::INTERVAL_1_DAY,
            Trader::INTERVAL_3_DAYS,
            Trader::INTERVAL_1_WEEK,
            Trader::INTERVAL_1_MONTH,
        ];
    }

    public function uiIntervals(): UiIntervals
    {
        return new UiIntervals($this->intervals(), Trader::INTERVAL_1_DAY);
    }

    public function isIntervalValid(Interval $interval): bool
    {
        return in_array((string)$interval, $this->intervals(), true);
    }

    public function availableTickers(
        string|array|null $quoteSymbol = null,
        string|array|null $baseSymbol = null,
        string|array|null $exceptQuoteSymbol = null,
        string|array|null $exceptBaseSymbol = null
    ): Collection
    {
        return collect([]);
    }

    protected function usdEquivalentSymbol(): string
    {
        return 'USDT';
    }

    protected function usdSymbols(): array
    {
        return ['USDT', 'BUSD'];
    }

    protected function usdPairCacheKey(string $symbol): string
    {
        return sprintf('%s.%s.usd', $this->exchange, $symbol);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function usdPairFromCache(string $symbol): ?string
    {
        return $this->cacheStore->get($this->usdPairCacheKey($symbol));
    }

    protected function usdPairToCache(string $symbol, string $usdSymbol): void
    {
        $this->cacheStore->forever($this->usdPairCacheKey($symbol), $usdSymbol);
    }

    abstract protected function createTicker($baseSymbol, $quoteSymbol): string;

    abstract protected function createTradeUrl($baseSymbol, $quoteSymbol): string;

    /**
     * @throws PsrInvalidArgumentException
     */
    public function symbol(string $symbol): Symbol
    {
        return new Symbol(
            is_null($usdSymbol = $this->usdPairFromCache($symbol))
                ? $this->symbolPrice($symbol, $usdSymbol)
                : $this->tickerPrice($this->createTicker($symbol, $usdSymbol)),
            $this->createTradeUrl($symbol, $usdSymbol)
        );
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function symbolPrice(string $symbol, string &$usdSymbol = null): string
    {
        if ($symbol === $this->usdEquivalentSymbol()) {
            return '1.00';
        }

        if (!is_null($usdSymbol = $this->usdPairFromCache($symbol))) {
            return $this->tickerPrice($this->createTicker($symbol, $usdSymbol));
        }

        $usdSymbols = $this->usdSymbols();
        while ($usdSymbol = array_shift($usdSymbols)) {
            try {
                return take($this->tickerPrice($this->createTicker($symbol, $usdSymbol)), function () use ($symbol, $usdSymbol) {
                    $this->usdPairToCache($symbol, $usdSymbol);
                });
            }
            catch (Throwable) {
                continue;
            }
        }

        throw new RuntimeException(sprintf('The symbol "%s" is invalid or not supported.', $symbol));
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function symbols(array $symbols): array
    {
        $pricing = [];
        $tickers = [];
        foreach ($symbols as $symbol) {
            if (is_null($usdSymbol = $this->usdPairFromCache($symbol))) {
                $pricing[$symbol] = new Symbol(
                    $this->symbolPrice($symbol, $usdSymbol),
                    $this->createTradeUrl($symbol, $usdSymbol)
                );
            }
            else {
                $tickers[$this->createTicker($symbol, $usdSymbol)] = [$symbol, $usdSymbol];
            }
        }
        if (count($tickers)) {
            foreach ($this->tickersPrice(array_keys($tickers)) as $ticker => $price) {
                [$symbol, $usdSymbol] = $tickers[$ticker];
                $pricing[$symbol] = new Symbol(
                    $price,
                    $this->createTradeUrl($symbol, $usdSymbol)
                );
            }
        }
        return $pricing;
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function symbolsPrice(array $symbols, array &$usdSymbols = null): array
    {
        $pricing = [];
        $tickers = [];
        $usdSymbols = [];
        foreach ($symbols as $symbol) {
            if (is_null($usdSymbol = $this->usdPairFromCache($symbol))) {
                $pricing[$symbol] = $this->symbolPrice($symbol, $usdSymbol);
            }
            else {
                $tickers[$this->createTicker($symbol, $usdSymbol)] = [$symbol, $usdSymbol];
            }
            $usdSymbols[$symbol] = $usdSymbol;
        }
        if (count($tickers)) {
            foreach ($this->tickersPrice(array_keys($tickers)) as $ticker => $price) {
                [$symbol] = $tickers[$ticker];
                $pricing[$symbol] = $price;
            }
        }
        return $pricing;
    }

    protected function recentPricesCacheKey(string $ticker, Interval $interval): string
    {
        return sprintf('%s.%s.%s', $this->exchange, $ticker, $interval);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentPricesFromCache(string $ticker, Interval $interval): ?array
    {
        return $this->cacheStore->get($this->recentPricesCacheKey($ticker, $interval));
    }

    protected function recentPricesToCache(string $ticker, Interval $interval, int $openTime, array $recentPrices): void
    {
        $this->cacheStore->forever($this->recentPricesCacheKey($ticker, $interval), [
            'latest_time' => $openTime,
            'recent_prices' => $recentPrices,
        ]);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentCachedPrices(string $ticker, Interval $interval, int $matchingLatestTime, ?array &$cachedRecentPrices = []): bool
    {
        if (is_null($cache = $this->recentPricesFromCache($ticker, $interval))) {
            $cachedRecentPrices = [];
            return false;
        }
        $cachedLatestTime = $cache['latest_time'] ?? 0;
        $cachedRecentPrices = $cache['recent_prices'] ?? [];
        if ($cachedLatestTime === 0 || !count($cachedRecentPrices)) {
            return false;
        }
        if ($cachedLatestTime !== $matchingLatestTime) {
            return false;
        }
        return true;
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function pushLatestPrice(LatestPrice $latestPrice): void
    {
        $price = $latestPrice->getPrice();
        if ($this->recentCachedPrices(
            $ticker = $latestPrice->getTicker(),
            $interval = $latestPrice->getInterval(),
            $interval->getPreviousOpenTimeOfExact($latestTime = $price->getOpenTime()),
            $cachedRecentPrices
        )) {
            if (count($cachedRecentPrices) >= Exchange::PRICE_LIMIT) {
                array_shift($cachedRecentPrices);
            }
            $cachedRecentPrices[] = $price->toArray();
            $this->recentPricesToCache(
                $ticker,
                $interval,
                $latestTime,
                $cachedRecentPrices
            );
        }
    }

    abstract protected function fetchPrices(string $ticker, Interval $interval, int $startTime = null, int $endTime = null, int $limit = Exchange::PRICE_LIMIT): array;

    protected function createPriceCollection(string $ticker, Interval $interval, array $prices, ?int $endTime = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection
    {
        return (new PriceCollection(
            $this->exchange,
            $ticker,
            $interval,
            $prices
        ))->fillMissingTimes($endTime, $limit);
    }

    public function hasPriceAt(string $ticker, ?int $time = null, ?Interval $interval = null): false|Price
    {
        $interval = $interval ?: new Interval(Trader::INTERVAL_1_MINUTE);
        $openTime = $interval->findOpenTimeOf($time);
        if (count($fetched = $this->fetchPrices($ticker, $interval, null, $openTime, 1)) > 0) {
            return Exchanger::exchange($this->exchange)->createPrice($fetched[0]);
        }
        if ($openTime >= $interval->findOpenTimeOf()) {
            return false;
        }
        if (count($fetched = $this->fetchPrices($ticker, $interval, $openTime, null, 1)) > 0) {
            return Exchanger::exchange($this->exchange)->createPrice($fetched[0]);
        }
        return false;
    }

    public function recentPricesAt(string $ticker, Interval $interval, ?int $time = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection
    {
        return $this->createPriceCollection(
            $ticker,
            $interval,
            $this->fetchPrices($ticker, $interval, null, $time, $limit),
            $time,
            $limit
        );
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function finalPrices(string $ticker, Interval $interval): PriceCollection
    {
        $openTime = $interval->getPreviousOpenTimeOfLatest();
        if ($this->recentCachedPrices($ticker, $interval, $openTime, $cachedRecentPrices)) {
            return $this->createPriceCollection($ticker, $interval, $cachedRecentPrices, $openTime);
        }
        return take($this->recentPricesAt($ticker, $interval, $openTime), function (PriceCollection $recent) use ($ticker, $interval, $openTime) {
            $this->recentPricesToCache(
                $ticker,
                $interval,
                $openTime,
                $recent->items()
            );
        });
    }

    abstract protected function createMarketOrder(string $ticker, string $amount, string $side): MarketOrder;

    public function buyMarket(string $ticker, string $amount): MarketOrder
    {
        return $this->createMarketOrder($ticker, $amount, static::ORDER_SIDE_BUY);
    }

    public function sellMarket(string $ticker, string $amount): MarketOrder
    {
        return $this->createMarketOrder($ticker, $amount, static::ORDER_SIDE_SELL);
    }
}
