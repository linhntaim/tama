<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Oscillators\Oscillator;
use App\Trading\Bots\Oscillators\RsiOscillator;
use Illuminate\Support\Collection;

class OscillatingBot extends Bot
{
    public const NAME = 'oscillating_bot';

    protected string $oscillatorName;

    protected Oscillator $oscillator;

    public function oscillatorName()
    {
        return $this->oscillatorName ?? $this->oscillatorName = $this->options['oscillator']['name'] ?? 'rsi';
    }

    protected function oscillator(): Oscillator
    {
        return $this->oscillator ?? $this->oscillator = (fn($class, $options) => new $class($options))(
                (fn() => match ($this->oscillatorName()) {
                    'rsi' => RsiOscillator::class,
                    default => take(RsiOscillator::class, function () {
                        $this->oscillatorName = 'rsi';
                    })
                })(),
                $this->options['oscillator']['options'] ?? []
            );
    }

    public function options(): array
    {
        return array_merge(
            parent::options(),
            [
                'oscillator' => $this->oscillator()->asOptions(),
            ]
        );
    }

    public function asSlug(): string
    {
        return implode('-', [
            $this->getName(),
            ...parent::options(),
            $this->oscillator()->asSlug(),
        ]);
    }

    /**
     * @return Collection<int, Indication>
     */
    protected function indicating(): Collection
    {
        return $this->oscillator()->run(
            $this->exchangeConnector()->getPrices(
                $this->ticker(),
                $this->interval()
            )
        );
    }
}
