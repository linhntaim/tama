<?php

/**
 * Base
 */

namespace App\Providers;

use App\Exceptions\Handler;
use App\Support\Client\Manager as ClientManager;
use App\Support\Http\Request;
use App\Support\Log\LineFormatter;
use App\Support\Log\LogManager;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRequest();
        $this->registerExceptionHandler();
        $this->registerLog();
        $this->registerClient();
    }

    protected function registerRequest()
    {
        $this->app->alias('request', Request::class);
    }

    protected function registerExceptionHandler()
    {
        // Override exception handler when running in console
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
        // Override log manager
        $this->app->singleton('log', function ($app) {
            return new LogManager($app);
        });
        Facade::clearResolvedInstance('log');
    }

    protected function registerClient()
    {
        $this->app->singleton(ClientManager::class, function ($app) {
            return new ClientManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureApp();
        $this->configureLog();
    }

    protected function configureApp()
    {
        $this->app['id'] = Str::uuid()->toString();
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
