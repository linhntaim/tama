<?php

namespace App\Support\Console;

use Closure;
use Illuminate\Support\Facades\Facade;

/**
 *
 * @method static int run(string $cmd, Closure $callback = null, bool $exceptionOnError = true)
 * @method static string cmd()
 * @method static int exitCode()
 * @method static bool successful()
 * @method static string|null output()
 *
 * @see Sheller
 */
class Shell extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'shell';
    }
}