<?php

namespace App\Http\Controllers\Api\Auth;

use App\Support\Http\Controllers\Api\Auth\RegisteredUserController as BaseRegisteredUserController;
use Closure;
use Illuminate\Http\Request;

class RegisteredUserController extends BaseRegisteredUserController
{
    protected function welcomeCreateUrlCallback(Request $request): ?Closure
    {
        return $request->has('login_url')
            ? function () use ($request) {
                return $request->input('login_url');
            }
            : null;
    }
}
