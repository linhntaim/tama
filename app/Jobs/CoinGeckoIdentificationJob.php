<?php

namespace App\Jobs;

use App\Support\Services\CoinGecko\Api\V3\CoinsApi as CoinGeckoCoinsApi;

class CoinGeckoIdentificationJob extends CoinIdentificationJob
{
    protected function batchByIndex(int $batchIndex): iterable
    {
        if ($batchIndex < 10
            && ($data = (new CoinGeckoCoinsApi())
                ->markets(
                    null,
                    null,
                    null,
                    null,
                    100,
                    $batchIndex + 1
                )) !== false) {
            return $data;
        }
        return [];
    }

    protected function handleBatchItem($item)
    {
        $this->dispatchEvent(
            $item['symbol'],
            'coin',
            $item['circulating_supply'],
            $item['total_supply'],
            $item['max_supply'],
        );
    }
}