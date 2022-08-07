<?php

namespace App\Support;

class ArrayReader
{
    public function __construct(
        protected array $data = []
    )
    {
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        return is_null($key) ? $this->data : data_get($this->data, $key, $default);
    }
}
