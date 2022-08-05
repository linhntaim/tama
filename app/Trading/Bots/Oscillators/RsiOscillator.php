<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Indication;
use Illuminate\Support\Collection;

class RsiOscillator extends Oscillator
{
    public const NAME = 'rsi';

    protected function createComponents()
    {
        $this->addComponent(new RsiComponent($this->options));
    }

    protected function process(Packet $packet): Packet
    {
        return $this->component(RsiComponent::NAME)->transmit($packet);
    }

    protected function output(Packet $packet): Collection
    {
        return $packet->get('transformers.rsi', [])
            ->filter(function (Indication $indication) {
                return $indication->get('value') != 0;
            });
    }
}
