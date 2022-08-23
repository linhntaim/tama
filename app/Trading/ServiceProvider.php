<?php

namespace App\Trading;

use App\Trading\Telegram\Client;
use GuzzleHttp\Client as HttpClient;
use NotificationChannels\Telegram\Telegram;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
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
