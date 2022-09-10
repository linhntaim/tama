<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;
use App\Trading\Bots\Exchanges\Interval;

class Indication extends ArrayReader
{
    public function __construct(
        float $value,
        int   $time,
        float $price,
        array $meta = [])
    {
        parent::__construct([
            'value' => $value,
            'time' => $time,
            'price' => $price,
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

    public function getActionTime(Interval $interval): int
    {
        return $interval->getNextOpenTimeOfExact($this->getTime());
    }

    public function getActionNow(Interval $interval): bool
    {
        return $interval->getNextOpenTimeOfExact($this->getTime()) === $interval->getLatestOpenTime();
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
