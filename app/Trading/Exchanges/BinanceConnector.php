<?php

namespace App\Trading\Exchanges;

use App\Trading\Prices\BinanceCandles;
use App\Trading\Prices\Prices;
use Binance\Exception\MissingArgumentException;
use Binance\Spot as BinanceSpot;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BinanceConnector extends Connector
{
    public const NAME = 'binance';

    protected BinanceSpot $spot;

    public function __construct()
    {
        $this->spot = new BinanceSpot();
    }

    public function isTickerValid(string $ticker): bool
    {
        try {
            $symbol = $this->spot->exchangeInfo([
                'symbol' => $ticker,
            ])['symbols'][0];

            return $symbol['status'] === 'TRADING'
                && in_array('SPOT', $symbol['permissions']);
        }
        catch (\Throwable) {
            return false;
        }
    }

    public function availableTickers(string|array|null $pattern = null): Collection
    {
        if (is_string($pattern)) {
            $pattern = strtoupper($pattern);
        }
        elseif (is_array($pattern)) {
            $pattern = array_map(fn($p) => strtoupper($p), $pattern);
        }
        return collect($this->spot->exchangeInfo()['symbols'])
            ->filter(function ($item) use ($pattern) {
                return $item['status'] === 'TRADING'
                    && in_array('SPOT', $item['permissions'])
                    && (is_null($pattern) || Str::is($pattern, $item['symbol']));
            })
            ->pluck('symbol');
    }

    /**
     * @throws MissingArgumentException
     */
    protected function getPrices(string $ticker, string $interval, string $lastAt = null, int $limit = 1000): array
    {
        return $this->spot->klines($ticker, $interval, [
            'limit' => $limit,
        ]);
    }

    protected function createPrices(array $prices, string $ticker, string $interval, string $lastAt = null): Prices
    {
        return new BinanceCandles(
            $ticker,
            $interval,
            $prices,
            $lastAt
        );
    }
}
