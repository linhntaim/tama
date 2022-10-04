<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Data\Indication;
use Illuminate\Support\Collection;

class StochRsiOscillator extends Oscillator
{
    public const NAME = 'stoch_rsi';

    protected function createComponents(): void
    {
        $this->addComponent(new StochRsiComponent($this->options));
    }

    protected function output(Packet $packet): Collection
    {
        return $packet->get('transformers.stoch_rsi', collect([]))
            ->filter(function (Indication $indication) {
                return num_ne($indication->getValue(), 0.0);
            });
    }
}