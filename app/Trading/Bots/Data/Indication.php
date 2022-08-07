<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;

class Indication extends ArrayReader
{
    public function __construct(float $value, string $time, float $price, bool $actionNow = false, array $meta = [])
    {
        parent::__construct([
            'value' => $value,
            'time' => $time,
            'price' => $price,
            'action_now' => $actionNow,
            'meta' => $meta,
        ]);
    }

    public function getValue(): float
    {
        return $this->get('value');
    }

    public function getTime(): string
    {
        return $this->get('time');
    }

    public function getPrice(): float
    {
        return $this->get('price');
    }

    public function getActionNow(): bool
    {
        return $this->get('action_now');
    }

    public function getActionSell(): bool
    {
        return $this->getValue() == 1.0;
    }

    public function getActionBuy(): bool
    {
        return $this->getValue() == -1.0;
    }

    public function getAction(): string
    {
        return match ($this->getValue()) {
            1.0 => 'SELL',
            -1.0 => 'BUY',
            default => 'UNKNOWN'
        };
    }

    public function getMeta(): array
    {
        return $this->get('meta');
    }
}
