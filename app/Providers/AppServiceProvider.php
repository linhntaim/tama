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
        $this->registerExceptionHandler();
        $this->registerLog();
    }

    protected function registerExceptionHandler()
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    protected function registerLog()
    {
        // Log formatter
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
        $this->configureLog();
    }

    protected function configureLog()
    {
        $config = config();
        $storageChannels = ['single', 'daily'];
        foreach ($config->get('logging.channels') as $channel => $_) {
            $config->set("logging.channels.$channel.formatter", 'starter_log_formatter');
            if (in_array($channel, $storageChannels)) {
                $config->set("logging.channels.$channel.permission", 0777);
            }
        }
    }
}
