<?php

namespace App\Trading\Bots;

use App\Support\Concerns\ClassHelper;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Data\Trade;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Exchanges\PriceCollection;
use App\Trading\Bots\Exchanges\PriceProvider;
use App\Trading\Bots\Exchanges\PriceProviderFactory;
use App\Trading\Bots\Exchanges\SwapperFactory;
use App\Trading\Bots\Exchanges\SwapProvider;
use App\Trading\Bots\Reporters\IReport;
use App\Trading\Bots\Reporters\PlainTextReporter;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;
use RuntimeException;

abstract class Bot
{
    use ClassHelper, BotSlug;

    public const NAME = '';

    private string $exchange;

    private PriceProvider $priceProvider;

    private SwapProvider $swapProvider;

    private string $ticker; // always uppercase

    private string $baseSymbol; // always uppercase

    private string $quoteSymbol; // always uppercase

    private Interval $interval;

    public function __construct(
        protected array $options = []
    )
    {
        $priceProvider = $this->priceProvider();
        if (!($this->options['safe_ticker'] ?? false)) {
            if (($ticker = $priceProvider->isTickerValid($this->ticker())) === false) {
                throw new RuntimeException('Ticker is invalid.');
            }
            [$this->options['base_symbol'], $this->options['quote_symbol']] = [$ticker->getBaseSymbol(), $ticker->getQuoteSymbol()];
        }
        if (!(($this->options['safe_interval'] ?? false) || $priceProvider->isIntervalValid($this->interval()))) {
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

    public function priceProvider(): PriceProvider
    {
        return $this->priceProvider ?? $this->priceProvider = PriceProviderFactory::create($this->exchange());
    }

    public function swapProvider(): SwapProvider
    {
        return $this->swapProvider ?? $this->swapProvider = SwapperFactory::create($this->exchange());
    }

    public function ticker(): string
    {
        return $this->ticker
            ?? $this->ticker = $this->options['ticker'];
    }

    public function baseSymbol(): string
    {
        return $this->baseSymbol
            ?? $this->baseSymbol = $this->options['base_symbol'];
    }

    public function quoteSymbol(): string
    {
        return $this->quoteSymbol
            ?? $this->quoteSymbol = $this->options['quote_symbol'];
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
            'ticker' => $this->ticker(),
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

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function fetchPrices(): PriceCollection
    {
        return $this->priceProvider()->recent(
            $this->ticker(),
            $this->interval()
        );
    }

    /**
     * @param PriceCollection $prices
     * @param int $latest
     * @return Collection<int, Indication>
     */
    abstract protected function indicating(PriceCollection $prices, int $latest = 0): Collection;

    /**
     * @param int $latest
     * @return Collection<int, Indication>
     * @throws PsrInvalidArgumentException
     */
    public function indicate(int $latest = 0): Collection
    {
        return $this->indicating($this->fetchPrices(), $latest);
    }

    abstract protected function indicatingNow(PriceCollection $prices): ?Indication;

    /**
     * @throws PsrInvalidArgumentException
     */
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
     * @throws PsrInvalidArgumentException
     */
    public function report(int|Collection $latest = 0): ?string
    {
        if (($indications = is_int($latest) ? $this->indicate($latest) : $latest)->count() === 0) {
            return null;
        }
        return $this->reporter()->report($this, $indications);
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function reportNow(?Indication $indication = null): ?string
    {
        if (is_null($indication ?: $indication = $this->indicateNow())) {
            return null;
        }
        return $this->reporter()->report($this, collect([$indication]));
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    public function tradeNow(
        float       $baseAmount,
        float       $quoteAmount,
        float       $buyingRisk = 1.0,
        float       $sellingRisk = 1.0,
        ?Indication $indication = null
    ): ?Trade
    {
        if (is_null($indication ?: $indication = $this->indicateNow())) {
            return null;
        }
        return match (true) {
            $indication->getActionBuy() => $this->buyNow($baseAmount, $buyingRisk, $indication),
            $indication->getActionSell() => $this->sellNow($quoteAmount, $sellingRisk, $indication),
            default => null
        };
    }

    protected function buyNow(
        float      $baseAmount,
        float      $buyingRisk,
        Indication $indication
    ): ?Trade
    {
        if (num_eq($buyAmount = $this->calculateBuyAmount($indication, $baseAmount, $buyingRisk), 0.0)) {
            return null;
        }
        [$fromAmount, $toAmount] = $this->swapProvider()->swap($this->baseSymbol, $this->quoteSymbol, $buyAmount);
        return new Trade([
            'base_amount' => -$fromAmount,
            'quote_amount' => $toAmount,
        ]);
    }

    protected function calculateBuyAmount(Indication $indication, float $amount, float $risk): bool
    {
        // Note: value of the indication is less than 0 and greater or equal -1
        if (num_gte(-$indication->getValue(), 1 - $risk)) { // accepted buy risk
            return $amount;
        }
        return 0.0;
    }

    protected function sellNow(
        float      $quoteAmount,
        float      $sellingRisk,
        Indication $indication
    ): ?Trade
    {
        if (num_eq($sellAmount = $this->calculateSellAmount($indication, $quoteAmount, $sellingRisk), 0.0)) {
            return null;
        }
        [$fromAmount, $toAmount] = $this->swapProvider()->swap($this->quoteSymbol, $this->baseSymbol, $sellAmount);
        return new Trade([
            'base_amount' => $toAmount,
            'quote_amount' => -$fromAmount,
        ]);
    }

    protected function calculateSellAmount(Indication $indication, float $amount, float $risk): float
    {
        // Note: value of the indication is greater than 0 and less than or equal 1
        if (num_gte($indication->getValue(), $risk)) { // accepted sell risk
            return $amount;
        }
        return 0.0;
    }
}
