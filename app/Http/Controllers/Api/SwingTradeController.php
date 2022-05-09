<?php

namespace App\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use App\Support\Services\Binance\Api\V3\MarketDataApi;
use App\Support\Trading\SwingTrading\RsiSwingTradeIndicator;

class SwingTradeController extends ApiController
{
    public function show(Request $request, $exchange, $indicator)
    {
        switch ($exchange) {
            case 'binance':
                return $this->showBinance($request, $indicator);
            default:
                $this->abort404();
        }
        return $this->responseFail($request);
    }

    protected function showBinance(Request $request, $indicator)
    {
        switch ($indicator) {
            case 'rsi':
                return $this->showBinanceRsi($request);
            default:
                $this->abort404();
        }
        return $this->responseFail($request);
    }

    protected function showBinanceRsi(Request $request)
    {
        $indicator = (new RsiSwingTradeIndicator(
            (new MarketDataApi())->candlestickData(
                $request->input('symbol', 'BTCUSDT'),
                $request->input('interval', MarketDataApi::INTERVAL_1_HOUR)
            )
        ));
        return $this->responseResource(
            $request,
            $request->has('last') ? [
                'swing_trade' => $indicator->getPossibleBuy(),
            ] : [
                'swing_trades' => $indicator->getPossibleBuys(),
            ]
        );
    }
}
