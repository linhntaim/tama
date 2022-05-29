<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use App\Support\Services\Binance\Api\V3\MarketDataApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExchangeController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->response($request, [
            'exchanges' => [
                [
                    'id' => 'binance',
                    'name' => 'Binance',
                ],
            ],
        ]);
    }

    public function tickerIndex(Request $request, $exchange): JsonResponse
    {
        switch ($exchange) {
            case 'binance':
                return $this->tickerIndexBinance($request);
            default:
                $this->abort404();
        }
        return $this->responseFail($request);
    }

    protected function tickerIndexBinance(Request $request): JsonResponse
    {
        $tickers = collect((new MarketDataApi())->exchangeInfo()['symbols'] ?? [])
            ->where('status', '=', 'TRADING')
            ->pluck('symbol')
            ->sort()
            ->all();
        return $this->response($request, [
            'tickers' => $tickers,
            'default' => 'BTCUSDT',
        ]);
    }

    public function intervalIndex(Request $request, $exchange): JsonResponse
    {
        switch ($exchange) {
            case 'binance':
                return $this->intervalIndexBinance($request);
            default:
                $this->abort404();
        }
        return $this->responseFail($request);
    }

    protected function intervalIndexBinance(Request $request): JsonResponse
    {
        return $this->response($request, [
            'intervals' => [
                MarketDataApi::INTERVAL_1_MINUTE,
                MarketDataApi::INTERVAL_3_MINUTES,
                MarketDataApi::INTERVAL_5_MINUTES,
                MarketDataApi::INTERVAL_15_MINUTES,
                MarketDataApi::INTERVAL_30_MINUTES,
                MarketDataApi::INTERVAL_1_HOUR,
                MarketDataApi::INTERVAL_2_HOURS,
                MarketDataApi::INTERVAL_4_HOURS,
                MarketDataApi::INTERVAL_6_HOURS,
                MarketDataApi::INTERVAL_8_HOURS,
                MarketDataApi::INTERVAL_12_HOURS,
                MarketDataApi::INTERVAL_1_DAY,
                MarketDataApi::INTERVAL_3_DAYS,
                MarketDataApi::INTERVAL_1_WEEK,
                MarketDataApi::INTERVAL_1_MONTH,
            ],
            'default' => MarketDataApi::INTERVAL_1_DAY,
        ]);
    }

    public function symbolShow(Request $request, $exchange, $symbol): JsonResponse
    {
        switch ($exchange) {
            case 'binance':
                return $this->binanceSymbolShow($request, $symbol);
            default:
                $this->abort404();
        }
        return $this->responseFail($request);
    }

    public function binanceSymbolShow(Request $request, $symbol): JsonResponse
    {
        $usdPairs = ['USDT', 'BUSD'];
        while ($usdSymbol = array_shift($usdPairs)) {
            if (($data = (new MarketDataApi())->tickerPrice($symbol . $usdSymbol)) !== false) {
                return $this->response($request, [
                    'symbol' => [
                        'ticker' => $data['symbol'],
                        'price' => floatval($data['price']),
                        'chart_url' => "https://www.binance.com/en/trade/{$symbol}_{$usdSymbol}",
                    ],
                ]);
            }
        }
        return $this->responseFail($request);
    }
}
