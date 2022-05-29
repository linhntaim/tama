<?php

namespace App\Support\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
