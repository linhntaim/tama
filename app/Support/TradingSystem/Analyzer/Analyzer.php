<?php

namespace App\Support\TradingSystem\Analyzer;

use App\Support\TradingSystem\Analyzer\Oscillators\Oscillator;
use App\Support\TradingSystem\Analyzer\Oscillators\RsiOscillator;
use App\Support\TradingSystem\Exchanges\BinanceConnector;
use App\Support\TradingSystem\Exchanges\Connector as ExchangeConnector;

class Analyzer
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    protected function exchange()
    {
        return $this->options['exchange'] ?? 'binance';
    }

    protected function exchangeConnector(): ExchangeConnector
    {
        return match ($this->exchange()) {
            default => new BinanceConnector(),
        };
    }

    protected function ticker()
    {
        return $this->options['ticker'] ?? 'BTCUSDT';
    }

    protected function interval()
    {
        return $this->options['interval'] ?? '1d';
    }

    protected function oscillator(): Oscillator
    {
        $oscillatorOption = $this->options['oscillator'] ?? [];
        return (fn($class, $options) => new $class($options))(
            (fn($name) => match ($name) {
                default => RsiOscillator::class
            })($oscillatorOption['name'] ?? 'rsi'),
            $oscillatorOption['options'] ?? []
        );
    }

    public function analyze(): array
    {
        $this->calculateSignalValue($history);
        return array_filter(array_map(fn($signalValue) => $this->determineSignal($signalValue), $history));
    }

    public function broadcast()
    {
        $this->broadcastSignal(
            $this->determineSignal(
                $this->calculateSignalValue()
            )
        );
    }

    protected function calculateSignalValue(?array &$history = null): float
    {
        return $this->oscillator()->signal(
            $this->exchangeConnector()->getPrices(
                $this->ticker(),
                $this->interval()
            ),
            $history
        );
    }

    protected function determineSignal(float $signalValue): ?Signal
    {
        if ($this->shouldBuy($signalValue)) {
            return new BuySignal($signalValue);
        }
        if ($this->shouldSell($signalValue)) {
            return new SellSignal($signalValue);
        }
        return null;
    }

    protected function shouldBuy(float $signalValue): bool
    {
        return $signalValue <= -0.75;
    }

    protected function shouldSell(float $signalValue): bool
    {
        return $signalValue > 0.75;
    }

    protected function broadcastSignal(?Signal $signal)
    {
        dd($signal);
    }
}
