<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;

class Indication extends ArrayReader
{
    public function __construct(
        float $value,
        int   $time,
        float $price,
        int   $actionTime,
        bool  $actionNow = false,
        array $meta = [])
    {
        parent::__construct([
            'value' => $value,
            'time' => $time,
            'price' => $price,
            'action_time' => $actionTime,
            'action_now' => $actionNow,
            'meta' => $meta,
        ]);
    }

    public function getValue(): float
    {
        return $this->get('value');
    }

    public function getTime(): int
    {
        return $this->get('time');
    }

    public function getPrice(): float
    {
        return $this->get('price');
    }

    public function getActionTime(): int
    {
        return $this->get('action_time');
    }

    public function getActionNow(): bool
    {
        return $this->get('action_now');
    }

    public function getActionSell(): bool
    {
        return num_gt($this->getValue(), 0);
    }

    public function getActionBuy(): bool
    {
        return num_lt($this->getValue(), 0);
    }

    public function getActionNeutral(): bool
    {
        return num_eq($this->getValue(), 0);
    }

    public function getAction(): string
    {
        return match (true) {
            $this->getActionSell() => 'SELL',
            $this->getActionBuy() => 'BUY',
            default => 'NEUTRAL'
        };
    }

    public function getMeta(): array
    {
        return $this->get('meta');
    }
}
