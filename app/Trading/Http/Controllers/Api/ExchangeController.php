<?php

namespace App\Trading\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Services\Binance\Api\V3\MarketDataApi;
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
        return $this->response($request, Exchanger::connector($exchange)->uiIntervals());
    }

    public function symbolIndex(Request $request, $exchange): JsonResponse
    {
        return $this->response($request, Exchanger::connector($exchange)->symbols($request->input('symbols', [])));
    }
}
