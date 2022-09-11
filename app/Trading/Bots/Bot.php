<?php

namespace App\Trading\Bots;

use App\Models\User;
use App\Support\Concerns\ClassHelper;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\BasicTicker;
use App\Trading\Bots\Exchanges\ConnectorInterface as ExchangeConnector;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\FakeConnector as FakeExchangeConnector;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\MarketOrder;
use App\Trading\Bots\Exchanges\PriceCollection;
use App\Trading\Bots\Exchanges\Ticker;
use App\Trading\Bots\Reporters\IReport;
use App\Trading\Bots\Reporters\PlainTextReporter;
use Illuminate\Support\Collection;
use RuntimeException;

abstract class Bot
{
    use ClassHelper, BotSlug;

    public const NAME = '';

    private string $exchange;

    private ExchangeConnector|FakeExchangeConnector $exchangeConnector;

    private Ticker $ticker; // always uppercase symbols

    private Interval $interval;

    public function __construct(
        protected array $options = []
    )
    {
        $exchangeConnector = $this->exchangeConnector();
        if (!($this->options['safe_ticker'] ?? false)) {
            if (($ticker = $exchangeConnector->isTickerValid($this->options['ticker'])) === false) {
                throw new RuntimeException('Ticker is invalid.');
            }
            [$this->options['base_symbol'], $this->options['quote_symbol']] = [$ticker->getBaseSymbol(), $ticker->getQuoteSymbol()];
        }
        if (!(($this->options['safe_interval'] ?? false) || $exchangeConnector->isIntervalValid($this->interval()))) {
            throw new RuntimeException('Interval is invalid.');
        }
    }

    final public function getName(): string
    {
        return static::NAME;
    }

    public function getDisplayName(): string
    {
        return $this->classFriendlyName();
    }

    public function exchange(): string
    {
        return $this->exchange
            ?? $this->exchange = $this->options['exchange'];
    }

    public function exchangeConnector(?User $user = null): ExchangeConnector|FakeExchangeConnector
    {
        return with(
            $this->exchangeConnector ?? $this->exchangeConnector = Exchanger::connector($this->exchange()),
            static fn(ExchangeConnector $connector) => is_null($user) ? $connector : $connector->withUser($user)
        );
    }

    public function useFakeExchangeConnector(): FakeExchangeConnector
    {
        $exchangeConnector = $this->exchangeConnector();
        if ($exchangeConnector instanceof FakeExchangeConnector) {
            return $exchangeConnector;
        }
        return tap(
            new FakeExchangeConnector($exchangeConnector),
            fn(ExchangeConnector $connector) => $this->exchangeConnector = $connector
        );
    }

    public function removeFakeExchangeConnector(): static
    {
        if ($this->exchangeConnector() instanceof FakeExchangeConnector) {
            unset($this->exchangeConnector);
        }
        return $this;
    }

    public function ticker(): Ticker
    {
        return $this->ticker
            ?? $this->ticker = new BasicTicker(
                $this->options['ticker'],
                $this->options['base_symbol'],
                $this->options['quote_symbol']
            );
    }

    public function baseSymbol(): string
    {
        return $this->ticker()->getBaseSymbol();
    }

    public function quoteSymbol(): string
    {
        return $this->ticker()->getQuoteSymbol();
    }

    public function interval(): Interval
    {
        return $this->interval
            ?? $this->interval = new Interval($this->options['interval']);
    }

    public function options(): array
    {
        return [
            'exchange' => $this->exchange(),
            'ticker' => (string)$this->ticker(),
            'base_symbol' => $this->baseSymbol(),
            'quote_symbol' => $this->quoteSymbol(),
            'interval' => (string)$this->interval(),
        ];
    }

    public function asOptions(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->options(),
        ];
    }

    protected function optionsAsSlug(): string
    {
        return $this->slugConcat(
            $this->exchange(),
            $this->ticker(),
            $this->interval()
        );
    }

    public function asSlug(): string
    {
        return $this->slugConcat(
            $this->getName(),
            $this->optionsAsSlug(),
        );
    }

    protected function fetchPrices(): PriceCollection
    {
        return $this->exchangeConnector()->finalPrices(
            $this->ticker(),
            $this->interval()
        );
    }

    /**
     * @param PriceCollection $prices
     * @param int $latest
     * @return Collection<int, Indication>
     */
    abstract public function indicating(PriceCollection $prices, int $latest = 0): Collection;

    /**
     * @param int $latest
     * @return Collection<int, Indication>
     */
    public function indicate(int $latest = 0): Collection
    {
        return $this->indicating($this->fetchPrices(), $latest);
    }

    abstract public function indicatingNow(PriceCollection $prices): ?Indication;

    public function indicateNow(): ?Indication
    {
        return $this->indicatingNow($this->fetchPrices());
    }

    protected function reporter(): IReport
    {
        return new PlainTextReporter();
    }

    /**
     * @param int|Collection<int, Indication> $latest
     * @return string|null
     */
    public function report(int|Collection $latest = 0): ?string
    {
        if (($indications = is_int($latest) ? $this->indicate($latest) : $latest)->count() === 0) {
            return null;
        }
        return $this->reporter()->report($this, $indications);
    }

    public function reportNow(?Indication $indication = null): ?string
    {
        if (is_null($indication ?: $indication = $this->indicateNow())) {
            return null;
        }
        return $this->reporter()->report($this, collect([$indication]));
    }

    public function tradeNow(
        User        $user,
        string      $baseAmount,
        string      $quoteAmount,
        float       $buyRisk = 0.0,
        float       $sellRisk = 0.0,
        ?Indication $indication = null
    ): ?MarketOrder
    {
        $indication = $indication ?: $this->indicateNow();
        if (is_null($indication)) {
            return null;
        }
        return match (true) {
            $indication->getActionBuy() => $this->buyNow($user, $quoteAmount, $indication, $buyRisk),
            $indication->getActionSell() => $this->sellNow($user, $baseAmount, $indication, $sellRisk),
            default => null
        };
    }

    public function tryToBuyNow(
        User        $user,
        string      $quoteAmount,
        float       $buyRisk = 0.0,
        ?Indication $indication = null
    ): ?MarketOrder
    {
        $indication = $indication ?: $this->indicateNow();
        if (is_null($indication) || !$indication->getActionBuy()) {
            return null;
        }
        return $this->buyNow($user, $quoteAmount, $indication, $buyRisk);
    }

    public function tryToSellNow(
        User        $user,
        string      $baseAmount,
        float       $sellRisk = 0.0,
        ?Indication $indication = null
    ): ?MarketOrder
    {
        $indication = $indication ?: $this->indicateNow();
        if (is_null($indication) || !$indication->getActionSell()) {
            return null;
        }
        return $this->sellNow($user, $baseAmount, $indication, $sellRisk);
    }

    protected function buyNow(
        User       $user,
        string     $quoteAmount,
        Indication $indication,
        float      $buyRisk = 0.0
    ): ?MarketOrder
    {
        if (num_eq($buyAmount = $this->calculateBuyAmount($indication, $quoteAmount, $buyRisk), 0)) {
            return null;
        }
        return $this->exchangeConnector($user)->buyMarket($this->ticker(), $buyAmount);
    }

    protected function calculateBuyAmount(Indication $indication, string $amount, float $risk = 0.0): string
    {
        // Note: value of the indication is less than 0 and greater or equal -1
        if (num_gte(-$indication->getValue(), 1 - $risk)) { // accepted buy risk
            return $amount;
        }
        return 0;
    }

    protected function sellNow(
        User       $user,
        string     $baseAmount,
        Indication $indication,
        float      $sellRisk = 0.0
    ): ?MarketOrder
    {
        if (num_eq($sellAmount = $this->calculateSellAmount($indication, $baseAmount, $sellRisk), 0)) {
            return null;
        }
        return $this->exchangeConnector($user)->sellMarket($this->ticker(), $sellAmount);
    }

    protected function calculateSellAmount(Indication $indication, string $amount, float $risk = 0.0): string
    {
        // Note: value of the indication is greater than 0 and less than or equal 1
        if (num_gte($indication->getValue(), $risk)) { // accepted sell risk
            return $amount;
        }
        return 0;
    }
}
