<?php

namespace App\Trading\Services\Binance\Api\V3;

use App\Trading\Support\Candle;
use App\Trading\Support\Candles;

class MarketDataApi extends Api
{
    public const INTERVAL_1_MINUTE = '1m';
    public const INTERVAL_3_MINUTES = '3m';
    public const INTERVAL_5_MINUTES = '5m';
    public const INTERVAL_15_MINUTES = '15m';
    public const INTERVAL_30_MINUTES = '30m';
    public const INTERVAL_1_HOUR = '1h';
    public const INTERVAL_2_HOURS = '2h';
    public const INTERVAL_4_HOURS = '4h';
    public const INTERVAL_6_HOURS = '6h';
    public const INTERVAL_8_HOURS = '8h';
    public const INTERVAL_12_HOURS = '12h';
    public const INTERVAL_1_DAY = '1d';
    public const INTERVAL_3_DAYS = '3d';
    public const INTERVAL_1_WEEK = '1w';
    public const INTERVAL_1_MONTH = '1M';

    public function ping(): bool|array
    {
        return $this
            ->get('ping')
            ->response();
    }

    public function time(): bool|array
    {
        return $this
            ->get('time')
            ->response();
    }

    public function exchangeInfo(
        string|array|null $symbol = null,
    ): bool|array
    {
        if (is_string($symbol)) {
            $params = [
                'symbol' => $symbol,
            ];
        }
        elseif (is_array($symbol)) {
            $params = [
                'symbols' => json_encode_readable($symbol),
            ];
        }
        else {
            $params = null;
        }
        return $this
            ->get('exchangeInfo', $params)
            ->response();
    }

    public function candlestickData(
        string $symbol,
        string $interval = null,
        ?int   $startTime = null,
        ?int   $endTime = null,
        ?int   $limit = null,
    ): bool|Candles
    {
        return transform(
            $this
                ->get('klines', filled_array(get_defined_vars(), [
                    'limit' => 500,
                ]))
                ->response(),
            function ($response) use ($interval, $endTime) {
                if (is_array($response)) {
                    return new Candles(
                        array_map(fn($candle) => new Candle(
                            array_associated_map($candle, [
                                'open_time',
                                'open' => function ($value) {
                                    return (float)$value;
                                },
                                'high' => function ($value) {
                                    return (float)$value;
                                },
                                'low' => function ($value) {
                                    return (float)$value;
                                },
                                'close' => function ($value) {
                                    return (float)$value;
                                },
                                'volume' => function ($value) {
                                    return (float)$value;
                                },
                                'close_time',
                                'quote_asset_volume' => function ($value) {
                                    return (float)$value;
                                },
                                'number_of_trades',
                                'take_buy_base_asset_volume' => function ($value) {
                                    return (float)$value;
                                },
                                'take_buy_quote_asset_volume' => function ($value) {
                                    return (float)$value;
                                },
                                'ignore' => function ($value) {
                                    return (float)$value;
                                },
                            ])
                        ), $response),
                        $interval,
                        $endTime
                    );
                }
                return $response;
            }
        );
    }

    public function tickerPrice(
        string|array|null $symbol = null,
    ): bool|array
    {
        if (is_string($symbol)) {
            $params = [
                'symbol' => $symbol,
            ];
        }
        elseif (is_array($symbol)) {
            $params = [
                'symbols' => json_encode_readable($symbol),
            ];
        }
        else {
            $params = null;
        }
        return $this
            ->get('ticker/price', $params)
            ->response();
    }
}
