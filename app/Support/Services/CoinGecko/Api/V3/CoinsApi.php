<?php

namespace App\Support\Services\CoinGecko\Api\V3;

class CoinsApi extends Api
{
    public function __construct()
    {
        $this->baseUrl .= '/coins';
    }

    public function list(
        ?bool $includePlatform = null,
    ): bool|array
    {
        return $this
            ->get('list', snaky_filled_array(get_defined_vars()))
            ->response();
    }

    public function show(
        string $id,
        ?bool  $localization = null,
        ?bool  $tickers = null,
        ?bool  $marketData = null,
        ?bool  $communityData = null,
        ?bool  $developerData = null,
        ?bool  $sparkline = null,
    ): bool|array
    {
        return $this
            ->get($id, snaky_filled_array(get_defined_vars(), [
                'localization' => true,
                'tickers' => true,
                'marketData' => true,
                'communityData' => true,
                'developerData' => true,
                'sparkline' => false,
            ]))
            ->response();
    }

    public function showTickers(
        string  $id,
        ?string $exchangeIds = null,
        ?string $includeExchangeLogo = null,
        ?int    $page = null,
        ?string $order = null,
        ?bool   $depth = null,
    ): bool|array
    {
        return $this
            ->get($id . '/tickers', snaky_filled_array(get_defined_vars(), [
                'order' => 'trust_score_desc',
            ]))
            ->response();
    }

    public function markets(
        ?string $vsCurrency = null,
        ?string $ids = null,
        ?string $category = null,
        ?string $order = null,
        ?int    $perPage = null,
        ?int    $page = null,
        ?bool   $sparkline = null,
        ?string $priceChangePercentage = null,
    ): bool|array
    {
        return $this
            ->get('markets', snaky_filled_array(get_defined_vars(), [
                'vsCurrency' => 'usd',
                'order' => 'market_cap_desc',
                'perPage' => 100,
                'page' => 1,
                'sparkline' => false,
            ]))
            ->response();
    }
}