<?php

namespace App\Trading\Bots\Pricing;

use App\Trading\Bots\Exchanges\Binance;
use Binance\Exception\MissingArgumentException;
use Binance\Spot;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BinancePriceProvider extends PriceProvider
{
    protected Spot $spot;

    public function __construct(CacheRepository|string|null $cache = 'redis')
    {
        parent::__construct(Binance::NAME, $cache);

        $this->spot = new Spot();
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
    public function fetch(string $ticker, Interval $interval, int $startTime = null, int $endTime = null, int $limit = 1000): array
    {
        return $this->spot->klines($ticker, (string)$interval, array_filter([
            'startTime' => $startTime,
            'endTime' => $endTime,
            'limit' => $limit,
        ]));
    }
}
