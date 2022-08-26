<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Data\Indication;
use Illuminate\Support\Collection;

class RsiOscillator extends Oscillator
{
    public const NAME = 'rsi';

    protected function createComponents(): void
    {
        $this->addComponent(new RsiComponent($this->options));
    }

    protected function output(Packet $packet): Collection
    {
        return $packet->get('transformers.rsi', [])
            ->filter(function (Indication $indication) {
                return $indication->getValue() !== 0.0;
            });
    }
}
