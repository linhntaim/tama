<?php

namespace App\Trading\Bots\Data;

use App\Support\ArrayReader;
use App\Trading\Bots\Exchanges\Interval;
use InvalidArgumentException;

class Indication extends ArrayReader
{
    public const ACTION_BUY = 'BUY';
    public const ACTION_SELL = 'SELL';
    public const ACTION_NEUTRAL = 'NEUTRAL';
    public const VALUE_NEUTRAL = 0.0;
    public const VALUE_BUY_MAX = -1.0;
    public const VALUE_SELL_MAX = 1.0;

    public function __construct(
        float  $value,
        int    $time,
        string $price,
        array  $meta = []
    )
    {
        if (num_gt($value, self::VALUE_SELL_MAX) || num_lt($value, self::VALUE_BUY_MAX)) {
            throw new InvalidArgumentException('Value must be less than or equal to 1.0 and greater than or equal to -1.0.');
        }
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

    public function getPrice(): string
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
        return num_gt($this->getValue(), self::VALUE_NEUTRAL);
    }

    public function getActionBuy(): bool
    {
        return num_lt($this->getValue(), self::VALUE_NEUTRAL);
    }

    public function getActionNeutral(): bool
    {
        return num_eq($this->getValue(), self::VALUE_NEUTRAL);
    }

    public function getAction(): string
    {
        return match (true) {
            $this->getActionSell() => self::ACTION_SELL,
            $this->getActionBuy() => self::ACTION_BUY,
            default => self::ACTION_NEUTRAL
        };
    }

    public function getMeta(): array
    {
        return $this->get('meta');
    }
}
