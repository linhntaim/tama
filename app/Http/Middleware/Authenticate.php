<?php

namespace App\Http\Middleware;

use App\Support\Http\Concerns\Requests;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    use Requests;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$this->advancedRequest()->expectsJson()) {
            return route('login');
        }
    }
}
