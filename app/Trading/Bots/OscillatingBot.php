<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Oscillators\Oscillator;
use App\Trading\Bots\Oscillators\RsiOscillator;

class OscillatingBot extends Bot
{
    protected string $oscillatorName;

    public function oscillatorName()
    {
        return $this->oscillatorName ?? $this->oscillatorName = $this->options['oscillator']['name'] ?? 'rsi';
    }

    protected function oscillator(): Oscillator
    {
        return (fn($class, $options) => new $class($options))(
            (fn() => match ($this->oscillatorName()) {
                'rsi' => RsiOscillator::class,
                default => take(RsiOscillator::class, function () {
                    $this->oscillatorName = 'rsi';
                })
            })(),
            $this->options['oscillator']['options'] ?? []
        );
    }

    public function discover(): array
    {
        return $this->oscillator()->run(
            $this->exchangeConnector()->getPrices(
                $this->ticker(),
                $this->interval()
            )
        );
    }

    protected function reporterClass(): string
    {
        return OscillatingBotReporter::class;
    }
}
