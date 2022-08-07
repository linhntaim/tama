<?php

namespace App\Support;

class ArrayWriter extends ArrayReader
{
    public function set(string $key, mixed $value): static
    {
        data_set($this->data, $key, $value);
        return $this;
    }
}
