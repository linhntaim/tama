<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;
use Illuminate\Support\Collection;

class Analysis extends ArrayReader
{
    /**
     * @param int $time
     * @param float $price
     * @param Collection<int, Signal> $signals
     * @param array $additional
     */
    public function __construct(int $time, string $price, Collection $signals, array $additional = [])
    {
        parent::__construct(array_merge([
            'time' => $time,
            'price' => $price,
            'signals' => $signals,
        ], $additional));
    }

    public function getTime(): int
    {
        return $this->get('time');
    }

    public function getPrice(): string
    {
        return $this->get('price');
    }

    /**
     * @return Collection<int, Signal>
     */
    public function getSignals(): Collection
    {
        return $this->get('signals');
    }

    public function hasSignal(string|array $types, string ...$moreTypes): bool
    {
        $types = array_merge((array)$types, $moreTypes);
        return $this->getSignals()->contains(function (Signal $signal) use ($types) {
            return in_array($signal->getType(), $types, true);
        });
    }
}
