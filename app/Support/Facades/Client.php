<?php

namespace App\Support\Facades;

use App\Support\Client\DateTimer;
use App\Support\Client\Manager;
use App\Support\Client\NumberFormatter;
use App\Support\Client\Settings;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Settings settings()
 * @method static DateTimer dateTimer()
 * @method static NumberFormatter numberFormatter()
 * @method static Manager settingsMerge(Settings|string|array|null $settings, bool $permanently = false, bool $apply = true)
 * @method static Manager settingsApply()
 * @method static bool settingsChanged()
 * @method static mixed settingsTemporary(Settings|string|array|null $settings, Closure $callback, ...$args)
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