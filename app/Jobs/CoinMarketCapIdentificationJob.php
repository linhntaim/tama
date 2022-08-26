<?php

namespace App\Jobs;

use App\Support\Services\CoinMarketCap\Api\V1\CryptocurrencyApi as CoinMarketCapCryptocurrencyApi;

class CoinMarketCapIdentificationJob extends CoinIdentificationJob
{
    protected function batchByIndex(int $batchIndex): iterable
    {
        if ($batchIndex < 10
            && ($data = (new CoinMarketCapCryptocurrencyApi())
                ->listingsLatest(
                    $batchIndex * 100 + 1,
                    100,
                )) !== false) {
            return $data;
        }
        return [];
    }

    protected function handleBatchItem($item): void
    {
        $this->dispatchEvent(
            $item['symbol'],
            is_null($item['platform'] ?? null) ? 'coin' : 'token',
            $item['circulating_supply'],
            $item['total_supply'],
            $item['max_supply'],
        );
    }
}
