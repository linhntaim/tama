<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;
use Illuminate\Support\Collection;

class IndicationMetaItem extends ArrayReader
{
    public function __construct(string $type, Collection $signals, array $additional = [])
    {
        parent::__construct(array_merge([
            'type' => $type,
            'signals' => $signals,
        ], $additional));
    }

    public function getType(): string
    {
        return $this->get('type');
    }

    /**
     * @return Collection<int, Signal>
     */
    public function getSignals(): Collection
    {
        return $this->get('signals');
    }
}
