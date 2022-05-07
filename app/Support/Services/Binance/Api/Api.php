<?php

namespace App\Support\Services\Binance\Api;

use App\Support\Services\Service;

abstract class Api extends Service
{
    protected string $baseUrl = 'https://api.binance.com/api';

    protected array $alternativeBaseUrls = [
        'https://api1.binance.com/api',
        'https://api2.binance.com/api',
        'https://api3.binance.com/api',
    ];
}