<?php

namespace App\Trading\Bots\Oscillators;

use InvalidArgumentException;

class Factory
{
    public static function create(string $name, array $options = []): Oscillator
    {
        return match ($name) {
            RsiOscillator::NAME => new RsiOscillator($options),
            default => throw new InvalidArgumentException(sprintf('Oscillator "%s" does not exist.', $name))
        };
    }
}
