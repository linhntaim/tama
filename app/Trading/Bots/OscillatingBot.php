<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Oscillators\Factory as OscillatorFactory;
use App\Trading\Bots\Oscillators\Oscillator;
use App\Trading\Bots\Pricing\PriceCollection;
use Illuminate\Support\Collection;

class OscillatingBot extends Bot
{
    public const NAME = 'oscillating_bot';

    protected Oscillator $oscillator;

    public function oscillator(): Oscillator
    {
        return $this->oscillator
            ?? $this->oscillator = OscillatorFactory::create(
                $this->options['oscillator']['name'],
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

    protected function optionsAsSlug(): string
    {
        return $this->slugConcat(...with(array_values(parent::options()), function ($options) {
            $options[] = $this->oscillator()->asSlug();
            return $options;
        }));
    }

    protected function indicating(PriceCollection $prices, int $latest = 0): Collection
    {
        return $this->oscillator()->run($prices, $latest);
    }

    protected function indicatingNow(PriceCollection $prices): ?Indication
    {
        return $this->oscillator()->run($prices)->first();
    }
}
