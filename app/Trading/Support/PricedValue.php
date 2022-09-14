<?php

namespace App\Trading\Support;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class PricedValue implements Arrayable, JsonSerializable
{
    /**
     * UTC-based
     *
     * @var string|null
     */
    protected ?string $time;

    protected ?float $price;

    protected float $strength;

    public function __construct(?string $time, ?float $price)
    {
        $this->time = $time;
        $this->price = $price;
    }

    public function toArray(): array
    {
        return [
            'time' => $this->time,
            'price' => $this->price,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
