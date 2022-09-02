<?php

namespace App\Trading\Bots\Exchanges;

use Binance\Exception\MissingArgumentException;
use Binance\Spot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class BinancePriceProvider extends PriceProvider
{
    protected Spot $spot;

    public function __construct(CacheRepository|string|null $cache = 'redis')
    {
        parent::__construct(Binance::NAME, $cache);

        $this->spot = new Spot();
    }

    public function isTickerValid(string $ticker): false|Ticker
    {
        try {
            $ticker = $this->spot->exchangeInfo([
                'symbol' => $ticker,
            ])['symbols'][0];

            return $ticker['status'] === 'TRADING' && in_array('SPOT', $ticker['permissions'], true)
                ? new BinanceTicker($ticker) : false;
        }
        catch (Throwable) {
            return false;
        }
    }

    public function availableTickers(string|array|null $pattern = null): Collection
    {
        return collect($this->spot->exchangeInfo()['symbols'])
            ->filter(function ($ticker) use ($pattern) {
                return $ticker['status'] === 'TRADING'
                    && in_array('SPOT', $ticker['permissions'], true)
                    && (is_null($pattern) || Str::is($pattern, $ticker['symbol']));
            })
            ->map(function (array $ticker) {
                return new BinanceTicker($ticker);
            });
    }

    /**
     * @throws MissingArgumentException
     */
    public function fetch(string $ticker, Interval $interval, int $startTime = null, int $endTime = null, int $limit = 1000): array
    {
        return $this->spot->klines($ticker, (string)$interval, array_filter([
            'startTime' => $startTime,
            'endTime' => $endTime,
            'limit' => $limit,
        ]));
    }
}
