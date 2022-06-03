<?php

namespace App\Http\Middleware;

use App\Support\Http\Requests;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    use Requests;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$this->advancedRequest()->expectsJson()) {
            return route('login');
        }
    }
}
