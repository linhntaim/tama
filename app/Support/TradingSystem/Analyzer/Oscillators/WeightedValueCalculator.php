<?php

namespace App\Support\TradingSystem\Analyzer\Oscillators;

use Illuminate\Support\Collection;

class WeightedValueCalculator
{
    protected array $weightedValues = [];

    public function addValue(float $value, float $weight = 1.0, string $label = ''): static
    {
        $this->weightedValues[] = [
            'v' => $value,
            'w' => $weight,
            'l' => $label,
        ];
        return $this;
    }

    public function value(): float
    {
        return count($this->weightedValues)
            ? (fn(Collection $weightedValues) => $weightedValues->map(fn($weightedValue) => $weightedValue['v'] * $weightedValue['w'])->sum() / $weightedValues->sum('w'))(collect($this->weightedValues))
            : 0.0;
    }
}
