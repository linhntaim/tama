<?php

namespace App\Trading\Bots\Oscillators;

class Packet
{
    protected array $values = [];

    public function set(string $name, mixed $value): static
    {
        $this->values[$name] = $value;
        return $this;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->values[$name] ?? $default;
    }
}
