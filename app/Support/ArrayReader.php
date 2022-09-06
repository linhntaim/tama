<?php

namespace App\Support;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class ArrayReader implements ArrayAccess, Arrayable
{
    public function __construct(
        protected array $data = []
    )
    {
    }

    public function get(?string $key = null, mixed $default = null): mixed
    {
        return data_get($this->data, $key, $default);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function __get(string $name)
    {
        return $this->offsetGet($name);
    }

    public function __set(string $name, $value): void
    {
        $this->offsetSet($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
