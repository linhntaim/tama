<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    protected static bool $ran = false;

    public static function ran(): bool
    {
        return static::$ran;
    }

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function handle($request, Closure $next)
    {
        static::$ran = true;
        return parent::handle($request, $next);
    }
}
