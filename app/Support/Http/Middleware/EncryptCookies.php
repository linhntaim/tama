<?php

namespace App\Support\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class EncryptCookies extends Middleware
{
    protected static bool $ran = false;

    public static function ran(): bool
    {
        return self::$ran;
    }

    public function handle($request, Closure $next): SymfonyResponse
    {
        self::$ran = true;
        return parent::handle($request, $next);
    }
}
