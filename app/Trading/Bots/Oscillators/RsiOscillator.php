<?php

namespace App\Trading\Bots\Oscillators;

class RsiOscillator extends Oscillator
{
    protected function process(Packet $packet): Packet
    {
        return (new RsiComponent($this->options))->transmit($packet);
    }

    protected function output(Packet $packet): array
    {
        return collect($packet->get('transformers.rsi', []))
            ->filter(function ($item) {
                return $item['value'] != 0;
            })
            ->all();
    }
}
