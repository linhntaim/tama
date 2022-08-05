<?php

namespace App\Trading\Bots;

class Indication
{
    public function __construct(
        protected array $data
    )
    {
    }

    public function get(?string $key = null): mixed
    {
        return is_null($key) ? $this->data : data_get($this->data, $key);
    }
}
