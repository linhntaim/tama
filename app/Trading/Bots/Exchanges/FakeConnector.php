<?php

namespace App\Trading\Bots\Exchanges;

use App\Models\User;
use Illuminate\Support\Collection;

class FakeConnector implements ConnectorInterface
{
    /**
     * @var array<string, string>
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

    public function availableTickers(
        string|array|null $quoteSymbol = null,
        string|array|null $baseSymbol = null,
        string|array|null $exceptQuoteSymbol = null,
        string|array|null $exceptBaseSymbol = null
    ): Collection
    {
        return $this->originConnector->availableTickers(
            $quoteSymbol,
            $baseSymbol,
            $exceptQuoteSymbol,
            $exceptBaseSymbol
        );
    }

    public function setTickerPrice(string $ticker, string $price): static
    {
        $this->currentPrices[$ticker] = $price;
        return $this;
    }

    public function tickerPrice(string $ticker): string
    {
        return $this->currentPrices[$ticker] ?? '1.0';
    }

    public function pushLatestPrice(LatestPrice $latestPrice): void
    {
        $this->originConnector->pushLatestPrice($latestPrice);
    }

    public function hasPricesAt(string $ticker, Interval $interval, ?int $time = null): bool|int
    {
        return $this->originConnector->hasPricesAt($ticker, $interval, $time);
    }

    public function recentPricesAt(string $ticker, Interval $interval, ?int $time = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection
    {
        return $this->originConnector->recentPricesAt($ticker, $interval, $time, $limit);
    }

    public function finalPrices(string $ticker, Interval $interval): PriceCollection
    {
        return $this->originConnector->finalPrices($ticker, $interval);
    }

    public function buyMarket(string $ticker, string $amount): MarketOrder
    {
        return new FakeMarketOrder($price = $this->tickerPrice($ticker), $amount, num_div($amount, $price));
    }

    public function sellMarket(string $ticker, string $amount): MarketOrder
    {
        return new FakeMarketOrder($price = $this->tickerPrice($ticker), $amount, num_mul($amount, $price), false);
    }
}
