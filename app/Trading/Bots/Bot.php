<?php

namespace App\Trading\Bots;

use App\Support\Concerns\ClassHelper;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Pricing\Interval;
use App\Trading\Bots\Pricing\PriceCollection;
use App\Trading\Bots\Pricing\PriceProvider;
use App\Trading\Bots\Pricing\PriceProviderFactory;
use Illuminate\Support\Collection;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;
use RuntimeException;

abstract class Bot
{
    use ClassHelper, BotSlug;

    public const NAME = '';

    private string $exchange;

    private PriceProvider $priceProvider;

    private string $ticker; // always uppercase

    private Interval $interval;

    public function __construct(
        protected array $options = []
    )
    {
        $priceProvider = $this->priceProvider();
        if (!(($this->options['safe_ticker'] ?? false) || $priceProvider->isTickerValid($this->ticker()))
            || !(($this->options['safe_interval'] ?? false) || $priceProvider->isIntervalValid($this->interval()))) {
            throw new RuntimeException('Ticker or interval is not valid.');
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

    public function ticker(): string
    {
        return $this->ticker
            ?? $this->ticker = $this->options['ticker'];
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
        return $this->slugConcat(...$this->options());
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
}
