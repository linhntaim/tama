<?php

/**
 * Base
 */

namespace App\Support\Client;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Settings settings()
 * @method static DateTimer dateTimer()
 * @method static NumberFormatter numberFormatter()
 * @method static Manager merge(Settings|array|null $settings)
 * @method static Manager temporary(Settings|array|null $settings, Closure $callback)
 *
 * @see Manager
 */
class Client extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return Manager::class;
    }
}