<?php

namespace App\Support\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    protected static bool $ran = false;

    public static function ran(): bool
    {
        return static::$ran;
    }

    public function handle($request, Closure $next)
    {
        static::$ran = true;
        return parent::handle($request, $next);
    }
}
