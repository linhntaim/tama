<?php

namespace App\Support\Http\Controllers\Api\Auth;

use App\Support\Auth\Notifications\WelcomeEmail;
use App\Support\Http\Controllers\ApiController;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;

class RegisteredUserController extends ApiController
{
    protected function welcomeCreateUrlCallback(Request $request): ?Closure
    {
        return null;
    }

    protected function welcomeMailCallback(Request $request): ?Closure
    {
        return null;
    }

    protected function verifyCreateUrlCallback(Request $request): ?Closure
    {
        return null;
    }

    protected function verifyMailCallback(Request $request): ?Closure
    {
        return null;
    }

    public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
    {
        WelcomeEmail::$createUrlCallback = $this->welcomeCreateUrlCallback($request);
        WelcomeEmail::$toMailCallback = $this->welcomeMailCallback($request);
        VerifyEmail::$createUrlCallback = $this->verifyCreateUrlCallback($request);
        VerifyEmail::$toMailCallback = $this->verifyMailCallback($request);

        event(new Registered($creator->create($request->all())));

        return app(RegisterResponse::class);
    }
}
