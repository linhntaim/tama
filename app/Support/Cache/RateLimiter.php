<?php

namespace App\Support\Cache;

use Closure;
use Illuminate\Cache\RateLimiter as BaseRateLimiter;
use Psr\SimpleCache\InvalidArgumentException;

class RateLimiter extends BaseRateLimiter
{
    /**
     * @throws InvalidArgumentException
     */
    protected function locked($key): bool
    {
        return $this->cache->has($key . ':lock_timer');
    }

    protected function lock($key, $lockSeconds = 3600): static
    {
        $this->cache->add(
            $this->cleanRateLimiterKey($key) . ':lock_timer', $this->availableAt($lockSeconds), $lockSeconds
        );
        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function lockAvailableIn($key)
    {
        return max(
            $this->availableIn($key),
            $this->cache->get($this->cleanRateLimiterKey($key) . ':lock_timer') - $this->currentTime()
        );
    }

    public function lockClear($key)
    {
        $this->clear($key);
        $this->cache->forget($this->cleanRateLimiterKey($key) . ':lock_timer');
    }

    public function attemptWithDelay($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
    {
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            echo 'sleep: ' . $this->availableIn($key) . PHP_EOL;
            sleep($this->availableIn($key));
        }

        return tap($callback() ?: true, function () use ($key, $decaySeconds) {
            $this->hit($key, $decaySeconds);
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function attemptWithLock($key, $maxAttempts, Closure $callback, $decaySeconds = 60, $lockSeconds = 3600)
    {
        if ($this->locked($key)) {
            return false;
        }
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            $this->lock($key, $lockSeconds);
            return false;
        }

        return tap($callback() ?: true, function () use ($key, $decaySeconds) {
            $this->hit($key, $decaySeconds);
        });
    }
}
