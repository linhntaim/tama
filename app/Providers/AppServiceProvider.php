<?php

namespace App\Providers;

use App\Exceptions\Handler;
use App\Support\Cache\RateLimiter;
use App\Support\Client\Manager as ClientManager;
use App\Support\Console\Sheller;
use App\Support\Log\LineFormatter;
use App\Support\Log\LogManager;
use App\Support\Notifications\ChannelManager;
use Illuminate\Cache\RateLimiter as BaseRateLimiter;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Notifications\ChannelManager as BaseChannelManager;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AppServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerAppId();
        $this->registerExceptionHandler();
        $this->registerLog();
        $this->registerCache();
        $this->registerNotification();
        $this->registerShell();
        $this->registerClient();
    }

    protected function registerAppId(): void
    {
        $this->app['id'] = Str::uuid()->toString();
    }

    protected function registerExceptionHandler(): void
    {
        // Override exception handler when running in console
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    protected function registerLog(): void
    {
        // Log formatter
        $this->app->bind('starter_log_formatter', function () {
            return tap(new LineFormatter(null, 'Y-m-d H:i:s', true, true), static function ($formatter) {
                $formatter->includeStacktraces();
            });
        });
        // Override log manager
        $this->app->singleton('log', function ($app) {
            return new LogManager($app);
        });
        Facade::clearResolvedInstance('log');
    }

    protected function registerNotification(): void
    {
        $this->app->singleton(BaseChannelManager::class, function ($app) {
            return new ChannelManager($app);
        });
        Facade::clearResolvedInstance(BaseChannelManager::class);
    }

    protected function registerCache(): void
    {
        $this->app->singleton(BaseRateLimiter::class, function ($app) {
            return new RateLimiter($app->make('cache')->driver(
                $app['config']->get('cache.limiter')
            ));
        });
    }

    protected function registerShell(): void
    {
        $this->app->singleton('shell', Sheller::class);
    }

    protected function registerClient(): void
    {
        $this->app->singleton(ClientManager::class, function ($app) {
            return new ClientManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        $this->configureLog();
        $this->configureMail();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function configureLog(): void
    {
        $config = config();
        $storageChannels = ['single', 'daily'];
        foreach ($config->get('logging.channels') as $channel => $_) {
            $config->set("logging.channels.$channel.formatter", 'starter_log_formatter');
            if (in_array($channel, $storageChannels, true)) {
                $config->set("logging.channels.$channel.permission", 0777);
            }
        }
    }

    protected function configureMail(): void
    {
        $alwaysTo = config_starter('mail.always_to');
        if ($alwaysTo['address']) {
            Mail::alwaysTo($alwaysTo['address'], $alwaysTo['name']);
        }
    }

    public function provides(): array
    {
        return [
            BaseRateLimiter::class,
        ];
    }
}
