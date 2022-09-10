<?php

namespace App\Trading\Bots\Exchanges;

use App\Models\User;
use Illuminate\Support\Collection;

interface ConnectorInterface
{
    public function withUser(User $user): static;

    public function isTickerValid(string $ticker): false|Ticker;

    public function isIntervalValid(Interval $interval): bool;

    public function availableTickers(string|array|null $pattern = null): Collection;

    public function tickerPrice(string $ticker): float;

    public function pushLatestPrice(LatestPrice $latestPrice): void;

    public function recentPricesAt(string $ticker, Interval $interval, ?int $time = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection;

    public function finalPrices(string $ticker, Interval $interval): PriceCollection;

    public function buyMarket(string $ticker, float $amount): MarketOrder;

    public function sellMarket(string $ticker, float $amount): MarketOrder;
}
