<?php

namespace App\Support\Http\Controllers\Api\Auth;

use App\Support\Auth\Notifications\WelcomeEmail;
use App\Support\Database\DatabaseTransaction;
use App\Support\Http\Controllers\ApiController;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Fortify\Contracts\RegisterResponse;
use Throwable;

class RegisteredUserController extends ApiController
{
    use DatabaseTransaction;

    protected function welcomeCreateUrlCallback(Request $request): ?Closure
    {
        return match (true) {
            $request->filled('login_uri') => fn() => url($request->input('login_uri')),
            $request->filled('login_url') => fn() => $request->input('login_url'),
            !Route::has('login') => fn() => url('auth/login'),
            default => null,
        };
    }

    protected function welcomeToMailCallback(Request $request): ?Closure
    {
        return null;
    }

    protected function verifyCreateUrlCallback(Request $request): ?Closure
    {
        return null;
    }

    protected function verifyToMailCallback(Request $request): ?Closure
    {
        return null;
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request, CreatesNewUsers $creator): RegisterResponse
    {
        WelcomeEmail::$createUrlCallback = $this->welcomeCreateUrlCallback($request);
        WelcomeEmail::$toMailCallback = $this->welcomeToMailCallback($request);
        VerifyEmail::$createUrlCallback = $this->verifyCreateUrlCallback($request);
        VerifyEmail::$toMailCallback = $this->verifyToMailCallback($request);

        $this->transactionStart();
        try {
            event(new Registered($creator->create($request->all())));
            return take(app(RegisterResponse::class), function () {
                $this->transactionComplete();
            });
        }
        catch (Throwable $exception) {
            $this->transactionAbort();
            throw $exception;
        }
    }
}
