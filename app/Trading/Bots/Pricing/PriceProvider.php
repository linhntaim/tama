<?php

namespace App\Trading\Bots\Pricing;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

abstract class PriceProvider
{
    protected Interval $interval;

    protected CacheRepository $cacheStore;

    protected string $recentCacheKey;

    public function __construct(
        protected string            $exchange,
        protected string            $ticker,
        Interval|string             $interval,
        CacheRepository|string|null $cache = 'redis'
    )
    {
        $this->interval = $interval instanceof Interval ? $interval : new Interval($interval);
        $this->cacheStore = $cache instanceof CacheRepository ? $cache : Cache::store($cache);
        $this->recentCacheKey = sprintf('%s.%s.%s', $this->exchange, $this->ticker, $this->interval);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentFromCache(): ?array
    {
        return $this->cacheStore->get($this->recentCacheKey);
    }

    protected function recentToCache(int $latestTime, array $recentPrices)
    {
        $this->cacheStore->forever($this->recentCacheKey, [
            'latest_time' => $latestTime,
            'recent_prices' => $recentPrices,
        ]);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function recentCached(int $matchingLatestTime, ?array &$cachedRecentPrices = []): bool
    {
        if (is_null($cache = $this->recentFromCache())) {
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
        if ($this->recentCached($this->interval->getPreviousLatestTimeOf($latestPrice->getTime()), $cachedRecentPrices)) {
            array_shift($cachedRecentPrices);
            array_push($cachedRecentPrices, $latestPrice->getPrice());
            $this->recentToCache($latestPrice->getTime(), $cachedRecentPrices);
        }
    }

    protected abstract function fetch(int $startTime = null, int $endTime = null, int $limit = 1000): array;

    public function get(int $startTime = null, int $endTime = null, int $limit = 1000): array
    {
        $prices = $this->fetch($startTime, $endTime, $limit);
        array_pop($prices);
        return $prices;
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function recent(): array
    {
        $latestTime = $this->interval->getPreviousLatestTime();
        if ($this->recentCached($latestTime, $cachedRecentPrices)) {
            return $cachedRecentPrices;
        }
        return take($this->get(), function ($recent) use ($latestTime) {
            $this->recentToCache($latestTime, $recent);
        });
    }
}
