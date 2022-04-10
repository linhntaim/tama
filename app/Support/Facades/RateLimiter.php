<?php

namespace App\Support\Facades;

use Closure;
use Illuminate\Support\Facades\RateLimiter as BaseRateLimiter;

/**
 *
 * @method static void lockClear($key)
 * @method static int lockAvailableIn($key)
 * @method static bool attemptWithLock($key, $maxAttempts, Closure $callback, $decaySeconds = 60, $lockSeconds = 3600)
 * @method static bool attemptWithDelay($key, $maxAttempts, Closure $callback, $decaySeconds = 60)
 *
 * @see \App\Support\Cache\RateLimiter
 */
class RateLimiter extends BaseRateLimiter
{
}