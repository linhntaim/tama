<?php

namespace App\Support;

class ArrayWriter extends ArrayReader
{
    public function set(string $key, mixed $value): static
    {
        data_set($this->data, $key, $value);
        return $this;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
}
