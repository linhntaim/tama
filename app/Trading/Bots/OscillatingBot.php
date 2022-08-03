<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Oscillators\Oscillator;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Exchanges\BinanceConnector;
use App\Trading\Exchanges\Connector as ExchangeConnector;

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

    public function indicate(): array
    {
        return $this->oscillator()->run(
            $this->exchangeConnector()->getPrices(
                $this->ticker(),
                $this->interval()
            )
        );
    }

    public function determine(): array
    {
        return $this->indicate();
    }

    public function broadcast(): array
    {
        return $this->determine();
    }
}
