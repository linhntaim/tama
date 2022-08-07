<?php

namespace App\Trading\Bots;

use App\Support\ClassTrait;
use App\Trading\Bots\Data\Indication;
use App\Trading\Exchanges\BinanceConnector;
use App\Trading\Exchanges\Connector as ExchangeConnector;
use App\Trading\Prices\Prices;
use Illuminate\Support\Collection;

abstract class Bot
{
    use ClassTrait;

    public const NAME = '';

    private string $exchange;

    private string $ticker;

    private string $interval;

    private int $latest;

    public function __construct(
        protected array $options = []
    )
    {
    }

    public final function getName(): string
    {
        return static::NAME;
    }

    public function getDisplayName(): string
    {
        return $this->classFriendlyName();
    }

    public function exchange()
    {
        return $this->exchange ?? $this->exchange = strtolower($this->options['exchange'] ?? 'binance');
    }

    protected function exchangeConnector(): ExchangeConnector
    {
        return match ($this->exchange()) {
            'binance' => new BinanceConnector(),
            default => take(new BinanceConnector(), function () {
                $this->exchange = 'binance';
            }),
        };
    }

    public function ticker()
    {
        return $this->ticker ?? $this->ticker = strtoupper($this->options['ticker'] ?? 'BTCUSDT');
    }

    public function interval()
    {
        return $this->interval ?? $this->interval = $this->options['interval'] ?? '1d';
    }

    public function options(): array
    {
        return [
            'exchange' => $this->exchange(),
            'ticker' => $this->ticker(),
            'interval' => $this->interval(),
        ];
    }

    public function asOptions(): array
    {
        return [
            'name' => $this->getName(),
            'options' => $this->options(),
        ];
    }

    public function asSlug(): string
    {
        return implode('-', [
            $this->getName(),
            ...$this->options(),
        ]);
    }

    protected function fetchPrices(?string $at = null): Prices
    {
        return $this->exchangeConnector()->getPrices(
            $this->ticker(),
            $this->interval()
        );
    }

    /**
     * @param Prices $prices
     * @param int $latest
     * @return Collection<int, Indication>
     */
    protected abstract function indicating(Prices $prices, int $latest = 0): Collection;

    /**
     * @param string|null $at
     * @param int $latest
     * @return Collection<int, Indication>
     */
    public function indicate(?string $at = null, int $latest = 0): Collection
    {
        return $this->indicating($this->fetchPrices($at), $latest);
    }

    protected abstract function indicatingNow(Prices $prices): ?Indication;

    public function indicateNow(?string $at = null): ?Indication
    {
        return $this->indicatingNow($this->fetchPrices($at));
    }
}
