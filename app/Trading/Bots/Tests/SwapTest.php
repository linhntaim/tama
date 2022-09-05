<?php

namespace App\Trading\Bots\Tests;

use App\Support\ArrayReader;
use App\Trading\Bots\Data\Indication;
use ArrayAccess;

class SwapTest extends ArrayReader implements ArrayAccess
{
    public function __construct(int $time, float $price, float $baseAmount, float $quoteAmount, ?Indication $indication = null)
    {
        parent::__construct([
            'time' => $time,
            'price' => $price,
            'base_amount' => num_floor($baseAmount, 18),
            'quote_amount' => num_floor($quoteAmount, 18),
            'indication' => $indication,
        ]);
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
}
