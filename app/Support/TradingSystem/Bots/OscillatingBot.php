<?php

namespace App\Support\TradingSystem\Bots;

use App\Support\TradingSystem\Bots\Oscillators\Oscillator;
use App\Support\TradingSystem\Bots\Oscillators\RsiOscillator;
use App\Support\TradingSystem\Exchanges\BinanceConnector;
use App\Support\TradingSystem\Exchanges\Connector as ExchangeConnector;

class OscillatingBot extends Bot
{
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

    public function act(): void
    {
        $this->broadcast(
            $this->determine(
                $this->indicate()
            )
        );
    }

    protected function indicate(): array
    {
        return $this->oscillator()->run(
            $this->exchangeConnector()->getPrices(
                $this->ticker(),
                $this->interval()
            )
        );
    }

    protected function determine(array $output): array
    {
        return $output;
    }

    protected function broadcast(array $output)
    {
        print_r($output);
    }
}
