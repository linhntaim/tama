<?php

namespace App\Support\Services\CoinGecko\Api\V3;

use App\Support\Services\Service;

abstract class Api extends Service
{
    protected string $baseUrl = 'https://api.coingecko.com/api/v3';

    protected int $maxAttempts = 50;
}