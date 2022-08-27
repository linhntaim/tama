<?php

namespace App\Trading;

use App\Trading\Services\Telegram\Client;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use NotificationChannels\Telegram\Telegram;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Telegram::class, static function () {
            return new Client(
                config('services.telegram-bot-api.token'),
                app(HttpClient::class),
                config('services.telegram-bot-api.base_uri')
            );
        });
    }
}
