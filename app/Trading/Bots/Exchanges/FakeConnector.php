<?php

namespace App\Trading\Bots\Exchanges;

use App\Models\User;
use Illuminate\Support\Collection;

class FakeConnector implements ConnectorInterface
{
    /**
     * @var array<string, float>
     */
    protected array $currentPrices = [];

    public function __construct(protected ConnectorInterface $originConnector)
    {
    }

    public function withUser(User $user): static
    {
        return $this;
    }

    public function isTickerValid(string $ticker): false|Ticker
    {
        return $this->originConnector->isTickerValid($ticker);
    }

    public function isIntervalValid(Interval $interval): bool
    {
        return $this->originConnector->isIntervalValid($interval);
    }

    public function availableTickers(array|string|null $pattern = null): Collection
    {
        return $this->originConnector->availableTickers($pattern);
    }

    public function pushLatestPrice(LatestPrice $latestPrice): void
    {
        $this->originConnector->pushLatestPrice($latestPrice);
    }

    public function recentPricesAt(string $ticker, Interval $interval, ?int $time = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection
    {
        return $this->originConnector->recentPricesAt($ticker, $interval, $time, $limit);
    }

    public function finalPrices(string $ticker, Interval $interval): PriceCollection
    {
        return $this->originConnector->finalPrices($ticker, $interval);
    }

    public function setTickerPrice(string $ticker, float $price): static
    {
        $this->currentPrices[$ticker] = $price;
        return $this;
    }

    public function buyMarket(string $ticker, float $amount): MarketOrder
    {
        return new FakeMarketOrder($amount, round($amount / ($this->currentPrices[$ticker] ?? 1), 2, PHP_ROUND_HALF_DOWN));
    }

    public function sellMarket(string $ticker, float $amount): MarketOrder
    {
        return new FakeMarketOrder($amount, round($amount * ($this->currentPrices[$ticker] ?? 1), 2, PHP_ROUND_HALF_DOWN));
    }
}
