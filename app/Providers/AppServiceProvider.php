<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Support\Log\LineFormatter;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
        $this->app->bind('starter_log_formatter', function () {
            return tap(new LineFormatter(null, 'Y-m-d H:i:s', true, true), function ($formatter) {
                $formatter->includeStacktraces();
            });
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
