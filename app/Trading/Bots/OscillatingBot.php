<?php

namespace App\Trading\Bots;

use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Exchanges\PriceCollection;
use App\Trading\Bots\Oscillators\Factory as OscillatorFactory;
use App\Trading\Bots\Oscillators\Oscillator;
use App\Trading\Bots\Reporters\IReport;
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
        return $this->slugConcat(parent::optionsAsSlug(), $this->oscillator()->asSlug());
    }

    public function indicating(PriceCollection $prices, int $latest = 0): Collection
    {
        return $this->oscillator()->run($prices, $latest);
    }

    public function indicatingNow(PriceCollection $prices): ?Indication
    {
        return $this->oscillator()->run($prices)->first();
    }

    protected function reporter(): IReport
    {
        return new OscillatingBotReporter();
    }
}
