<?php

namespace App\Support\Http\Middleware;

use App\Support\Http\Request;
use Closure;

class EnableDebug
{
    public function handle(Request $request, Closure $next)
    {
        if (config_starter('app.debug_from_request')
            && ($request->has('x_debug')
                || $request->headers->has('x-debug')
                || $request->cookies->has(name_starter('debug'))
            )) {
            config(['app.debug' => true]);
        }
        return $next($request);
    }
}