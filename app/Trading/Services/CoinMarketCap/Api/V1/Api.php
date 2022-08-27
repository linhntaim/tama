<?php

namespace App\Trading\Services\CoinMarketCap\Api\V1;

use App\Support\Services\Service;
use Illuminate\Http\Client\PendingRequest;

abstract class Api extends Service
{
    protected string $baseUrl = 'https://pro-api.coinmarketcap.com/v1';

    protected string $apiKey;

    protected int $maxAttempts = 30;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey ?: config('services.coin_market_cap.api_key');
    }

    protected function createRequest(): PendingRequest
    {
        return parent::createRequest()->withHeaders([
            'X-CMC_PRO_API_KEY' => $this->apiKey,
        ]);
    }

    protected function response(): bool|array|string
    {
        if (($response = parent::response()) !== false) {
            if (($response['status']['error_code'] ?? 0) !== 0) {
                return false;
            }
            return $response['data'] ?? false;
        }
        return false;
    }
}
