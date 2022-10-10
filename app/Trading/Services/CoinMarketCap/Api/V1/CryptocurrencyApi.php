<?php

namespace App\Trading\Services\CoinMarketCap\Api\V1;

class CryptocurrencyApi extends Api
{
    public function getBaseUrl(): string
    {
        return parent::getBaseUrl() . '/cryptocurrency';
    }

    public function map(
        ?string $listingStatus = null,
        ?int    $start = null,
        ?int    $limit = null,
        ?string $sort = null,
        ?string $symbol = null,
        ?string $aux = null,
    ): bool|array
    {
        return $this
            ->get('map', snaky_filled_array(get_defined_vars(), [
                'listingStatus' => 'active',
                'start' => 1,
                'sort' => 'id',
                'aux' => 'platform,first_historical_data,last_historical_data,is_active',
            ]))
            ->response();
    }

    public function listingsLatest(
        ?int    $start = null,
        ?int    $limit = null,
        ?float  $priceMin = null,
        ?float  $priceMax = null,
        ?float  $marketCapMin = null,
        ?float  $marketCapMax = null,
        ?float  $volume24hMin = null,
        ?float  $volume24hMax = null,
        ?float  $circulatingSupplyMin = null,
        ?float  $circulatingSupplyMax = null,
        ?float  $percentChange24hMin = null,
        ?float  $percentChange24hMax = null,
        ?string $convert = null,
        ?string $convertId = null,
        ?string $sort = null,
        ?string $sortDir = null,
        ?string $cryptocurrencyType = null,
        ?string $tag = null,
        ?string $aux = null,
    ): bool|array
    {
        return $this
            ->get('listings/latest', snaky_filled_array(get_defined_vars(), [
                'start' => 1,
                'limit' => 100,
                'sort' => 'market_cap',
                'cryptocurrencyType' => 'all',
                'tag' => 'all',
                'aux' => 'num_market_pairs,cmc_rank,date_added,tags,platform,max_supply,circulating_supply,total_supply',
            ]))
            ->response();
    }
}
