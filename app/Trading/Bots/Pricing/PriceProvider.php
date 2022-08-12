<?php

namespace App\Trading\Bots\Pricing;

use App\Trading\Trader;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

abstract class PriceProvider
{
    protected CacheRepository $cacheStore;

    public function __construct(
        protected string            $exchange,
        CacheRepository|string|null $cache = 'redis'
    )
    {
        $this->cacheStore = $cache instanceof CacheRepository ? $cache : Cache::store($cache);
    }

    public function isTickerValid(string $ticker): bool
    {
        return false;
    }

    public function isIntervalValid(Interval $interval): bool
    {
        return in_array((string)$interval, [
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
        ]);
    }

    public function availableTickers(string|array|null $pattern = null): Collection
    {
        return collect([]);
    }

    protected function recentCacheKey(string $ticker, Interval $interval): string
    {
        return sprintf('%s.%s.%s', $this->exchange, $ticker, $interval);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentFromCache(string $ticker, Interval $interval): ?array
    {
        return $this->cacheStore->get($this->recentCacheKey($ticker, $interval));
    }

    protected function recentToCache(string $ticker, Interval $interval, int $latestTime, array $recentPrices)
    {
        $this->cacheStore->forever($this->recentCacheKey($ticker, $interval), [
            'latest_time' => $latestTime,
            'recent_prices' => $recentPrices,
        ]);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentCached(string $ticker, Interval $interval, int $matchingLatestTime, ?array &$cachedRecentPrices = []): bool
    {
        if (is_null($cache = $this->recentFromCache($ticker, $interval))) {
            return false;
        }
        $cachedLatestTime = $cache['latest_time'] ?? 0;
        $cachedRecentPrices = $cache['recent_prices'] ?? [];
        if ($cachedLatestTime == 0 || !count($cachedRecentPrices)) {
            return false;
        }
        if ($cachedLatestTime != $matchingLatestTime) {
            return false;
        }
        return true;
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function pushLatest(LatestPrice $latestPrice)
    {
        if ($this->recentCached(
            $ticker = $latestPrice->getTicker(),
            $interval = $latestPrice->getInterval(),
            $interval->getPreviousLatestTimeOf($latestTime = $latestPrice->getTime()),
            $cachedRecentPrices
        )) {
            array_shift($cachedRecentPrices);
            array_push($cachedRecentPrices, $latestPrice->getPrice());
            $this->recentToCache(
                $ticker,
                $interval,
                $latestTime,
                $cachedRecentPrices
            );
        }
    }

    protected abstract function fetch(string $ticker, Interval $interval, int $startTime = null, int $endTime = null, int $limit = 1000): array;

    public function recentAt(string $ticker, Interval $interval, int $at = null, int $limit = 999): PriceCollection
    {
        $prices = $this->fetch($ticker, $interval, null, $at, $limit + 1);
        array_pop($prices);
        return PriceCollectionFactory::create(
            $this->exchange,
            $ticker,
            $interval,
            $prices,
            $interval->getPreviousLatestTimes($limit)
        );
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function recent(string $ticker, Interval $interval): PriceCollection
    {
        $latestTime = $interval->getPreviousLatestTime();
        if ($this->recentCached($ticker, $interval, $latestTime, $cachedRecentPrices)) {
            return PriceCollectionFactory::create(
                $this->exchange,
                $ticker,
                $interval,
                $cachedRecentPrices,
                $interval->getPreviousLatestTimes(count($cachedRecentPrices))
            );
        }
        return take($this->recentAt($ticker, $interval), function (PriceCollection $recent) use ($ticker, $interval, $latestTime) {
            $this->recentToCache(
                $ticker,
                $interval,
                $latestTime,
                $recent->items()
            );
        });
    }
}
