<?php

namespace App\Trading\Bots\Exchanges;

use App\Models\User;
use Illuminate\Support\Collection;

interface ConnectorInterface
{
    public function withUser(User $user): static;

    public function isTickerValid(string $ticker): false|Ticker;

    public function intervals(): array;

    public function uiIntervals(): UiIntervals;

    public function isIntervalValid(Interval $interval): bool;

    /**
     * @param string|string[]|null $quoteSymbol
     * @param string|string[]|null $baseSymbol
     * @param string|string[]|null $exceptQuoteSymbol
     * @param string|string[]|null $exceptBaseSymbol
     * @return Collection<int, Ticker>
     */
    public function availableTickers(
        string|array|null $quoteSymbol = null,
        string|array|null $baseSymbol = null,
        string|array|null $exceptQuoteSymbol = null,
        string|array|null $exceptBaseSymbol = null
    ): Collection;

    public function createTicker(string $baseSymbol, string $quoteSymbol): string;

    public function createTradeUrl(string $baseSymbol, string $quoteSymbol): string;

    public function symbol(string $symbol): Symbol;

    public function symbolPrice(string $symbol, string &$usdSymbol = null): string;

    /**
     * @param string[] $symbols
     * @return array<string, Symbol>
     */
    public function symbols(array $symbols): array;

    /**
     * @param string[] $symbols
     * @return array<string, string>
     */
    public function symbolsPrice(array $symbols, array &$usdSymbols = null): array;

    /**
     * @param string[] $tickers
     * @return array<string, string>
     */
    public function tickersPrice(array $tickers): array;

    public function tickerPrice(string $ticker): string;

    public function pushLatestPrice(LatestPrice $latestPrice): void;

    public function hasPriceAt(string $ticker, ?int $time = null, ?Interval $interval = null): false|Price;

    public function recentPricesAt(string $ticker, Interval $interval, ?int $time = null, int $limit = Exchange::PRICE_LIMIT): PriceCollection;

    public function finalPrices(string $ticker, Interval $interval): PriceCollection;

    public function buyMarket(string $ticker, string $amount): MarketOrder;

    public function sellMarket(string $ticker, string $amount): MarketOrder;
}
