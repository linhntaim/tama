<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;

class Signal extends ArrayReader
{
    public function __construct(string $type, string $strength, array $additional = [])
    {
        parent::__construct(array_merge([
            'type' => $type,
            'strength' => $strength,
        ], $additional));
    }

    public function getType(): string
    {
        return $this->get('type');
    }

    public function getStrength(): string
    {
        return $this->get('strength');
    }
}
