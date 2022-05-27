<?php

namespace App\Support\Http\Controllers\Api\Auth;

use App\Support\Http\Controllers\ApiController;
use Closure;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class PasswordResetLinkController extends ApiController
{
    protected function createUrlCallback(Request $request): ?Closure
    {
        return match (true) {
            $request->filled('reset_uri') => fn($notifiable, $token) => url(uri(rawurldecode($request->input('reset_uri')), [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)),
            $request->filled('reset_url') => fn($notifiable, $token) => uri(rawurldecode($request->input('reset_url')), [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]),
            !Route::has('password.reset') => fn($notifiable, $token) => url(uri('auth/reset-password/{token}', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false)),
            default => null,
        };
    }

    protected function toMailCallback(Request $request): ?Closure
    {
        return null;
    }

    public function store(Request $request): Responsable
    {
        $request->validate([Fortify::email() => 'required|email']);

        ResetPassword::$createUrlCallback = $this->createUrlCallback($request);
        ResetPassword::$toMailCallback = $this->toMailCallback($request);

        $status = $this->broker()->sendResetLink(
            $request->only(Fortify::email())
        );

        return $status == Password::RESET_LINK_SENT
            ? app(SuccessfulPasswordResetLinkRequestResponse::class, ['status' => $status])
            : app(FailedPasswordResetLinkRequestResponse::class, ['status' => $status]);
    }

    protected function broker(): PasswordBroker
    {
        return Password::broker(config('fortify.passwords'));
    }
}
