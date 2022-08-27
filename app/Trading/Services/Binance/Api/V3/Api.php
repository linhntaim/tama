<?php

namespace App\Trading\Services\Binance\Api\V3;

use App\Trading\Services\Binance\Api\Api as BaseApi;

class Api extends BaseApi
{
    protected int $maxAttempts = 1200;

    public function getBaseUrl(): string
    {
        return parent::getBaseUrl() . '/v3';
    }
}
