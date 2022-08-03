<?php

namespace App\Trading\Bots;

use App\Support\ClassTrait;
use App\Trading\Exchanges\BinanceConnector;
use App\Trading\Exchanges\Connector as ExchangeConnector;

abstract class Bot
{
    use ClassTrait;

    protected string $exchange;

    protected string $ticker;

    protected string $interval;

    protected int $latest;

    public function __construct(
        protected array $options = []
    )
    {
    }

    public function getDisplayName(): string
    {
        return $this->classFriendlyName();
    }

    public function exchange()
    {
        return $this->exchange ?? $this->exchange = $this->options['exchange'] ?? 'binance';
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
        return $this->ticker ?? $this->ticker = $this->options['ticker'] ?? 'BTCUSDT';
    }

    public function interval()
    {
        return $this->interval ?? $this->interval = $this->options['interval'] ?? '1d';
    }

    public function latest()
    {
        return $this->latest ?? $this->latest = $this->options['latest'] ?? 0;
    }

    public function unlimited(): bool
    {
        return $this->latest() == 0;
    }

    public abstract function discover(): array;

    public function indicate(): array
    {
        $data = array_reverse($this->discover());
        return $this->latest() > 0 ? array_slice($data, 0, $this->latest()) : $data;
    }

    protected function reporterClass(): string
    {
        return PlainTextBotReporter::class;
    }

    protected function reporter(): BotReporter
    {
        $class = $this->reporterClass();
        return new $class($this);
    }

    public function report(): string
    {
        return $this->reporter()->report();
    }
}
