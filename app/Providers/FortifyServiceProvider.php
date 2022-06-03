<?php

namespace App\Providers;

use App\Actions\Fortify\CompletePasswordReset;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Support\Http\Responses\FailedPasswordResetLinkRequestResponse;
use App\Support\Http\Responses\FailedPasswordResetResponse;
use App\Support\Http\Responses\LoginResponse;
use App\Support\Http\Responses\LogoutResponse;
use App\Support\Http\Responses\PasswordResetResponse;
use App\Support\Http\Responses\RegisterResponse;
use App\Support\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Actions\CompletePasswordReset as BaseCompletePasswordReset;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResponseBindings();
    }

    protected function registerResponseBindings()
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        Facade::clearResolvedInstance(LoginResponseContract::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        Facade::clearResolvedInstance(LogoutResponseContract::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        Facade::clearResolvedInstance(RegisterResponseContract::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponseContract::class, SuccessfulPasswordResetLinkRequestResponse::class);
        Facade::clearResolvedInstance(SuccessfulPasswordResetLinkRequestResponseContract::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponseContract::class, FailedPasswordResetLinkRequestResponse::class);
        Facade::clearResolvedInstance(FailedPasswordResetLinkRequestResponseContract::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);
        Facade::clearResolvedInstance(PasswordResetResponseContract::class);
        $this->app->singleton(FailedPasswordResetResponseContract::class, FailedPasswordResetResponse::class);
        Facade::clearResolvedInstance(FailedPasswordResetResponseContract::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        app()->singleton(BaseCompletePasswordReset::class, CompletePasswordReset::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string)$request->input('email');

            return Limit::perMinute(5)->by($email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
