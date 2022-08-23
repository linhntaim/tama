<?php

namespace App\Support\Http\Middleware;

use App\Support\Facades\App;
use App\Support\Http\Concerns\Abort;
use Closure;
use Illuminate\Http\Request;

class DisableInProduction
{
    use Abort;

    public function handle(Request $request, Closure $next)
    {
        if (App::isProduction()) {
            $this->abort404();
        }
        return $next($request);
    }
}
