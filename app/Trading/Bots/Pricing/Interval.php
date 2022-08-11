<?php

namespace App\Trading\Bots\Pricing;

use Carbon\Carbon;
use InvalidArgumentException;

class Interval
{
    protected string $unit;

    protected int $number;

    public function __construct(
        protected string $interval
    )
    {
    }

    public function getUnit(): string
    {
        return $this->unit ?? $this->unit = substr($this->interval, -1);
    }

    public function getNumber(): int
    {
        return $this->number ?? $this->number = (int)$this->interval;
    }

    public function getLatestTime(int $index = 0): int
    {
        $index = $index >= 0 ? 0 : -$index;
        $now = Carbon::now();
        $timestampNow = $now->getTimestamp() + 62135596800; // full timestamp from 01/01/0001 00:00:00
        return (match ($this->getUnit()) {
            'm' => $now
                ->subMinutes(((int)($timestampNow / 60)) % $this->getNumber() + $index * $this->getNumber())
                ->second(0),
            'h' => $now
                ->subHours(((int)($timestampNow / 3600)) % $this->getNumber() + $index * $this->getNumber())
                ->minute(0)
                ->second(0),
            'd' => $now
                ->subDays(((int)($timestampNow / 3600 * 24)) % $this->getNumber() + $index * $this->getNumber())
                ->hour(0)
                ->minute(0)
                ->second(0),
            'w' => $now
                ->subDays(((int)($timestampNow / 3600 * 24)) % ($this->getNumber() * 7) + $index * $this->getNumber() * 7)
                ->hour(0)
                ->minute(0)
                ->second(0),
            'M' => $now
                ->subMonths((($now->year - 1) * 12 + $now->month) % $this->getNumber() + $index * $this->getNumber())
                ->day(1)
                ->hour(0)
                ->minute(0)
                ->second(0),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        })->getTimestamp();
    }

    public function getPreviousLatestTime(): int
    {
        return $this->getLatestTime(-1);
    }

    public function getPreviousLatestTimeOf(int $latestTime): int
    {
        return (match ($this->getUnit()) {
            'm' => Carbon::createFromTimestamp($latestTime)->subMinutes($this->getNumber()),
            'h' => Carbon::createFromTimestamp($latestTime)->subHours($this->getNumber()),
            'd' => Carbon::createFromTimestamp($latestTime)->subDays($this->getNumber()),
            'w' => Carbon::createFromTimestamp($latestTime)->subDays($this->getNumber() * 7),
            'M' => Carbon::createFromTimestamp($latestTime)->subMonths($this->getNumber()),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        })->getTimestamp();
    }

    public function __toString(): string
    {
        return $this->interval;
    }
}
