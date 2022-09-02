<?php

namespace App\Trading\Bots\Exchanges;

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
                ->subMinutes(int_exp($timestampNow / 60) % $this->getNumber() + $index * $this->getNumber())
                ->second(0),
            'h' => $now
                ->subHours(int_exp($timestampNow / 3600) % $this->getNumber() + $index * $this->getNumber())
                ->minute(0)
                ->second(0),
            'd' => $now
                ->subDays(int_exp($timestampNow / 3600 * 24) % $this->getNumber() + $index * $this->getNumber())
                ->hour(0)
                ->minute(0)
                ->second(0),
            'w' => $now
                ->subDays(int_exp($timestampNow / 3600 * 24) % ($this->getNumber() * 7) + $index * $this->getNumber() * 7)
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

    public function getNextLatestTimeOf(int $latestTime): int
    {
        return (match ($this->getUnit()) {
            'm' => Carbon::createFromTimestamp($latestTime)->addMinutes($this->getNumber()),
            'h' => Carbon::createFromTimestamp($latestTime)->addHours($this->getNumber()),
            'd' => Carbon::createFromTimestamp($latestTime)->addDays($this->getNumber()),
            'w' => Carbon::createFromTimestamp($latestTime)->addDays($this->getNumber() * 7),
            'M' => Carbon::createFromTimestamp($latestTime)->addMonths($this->getNumber()),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        })->getTimestamp();
    }

    /**
     * @param int $limit
     * @param int $index
     * @return int[]
     */
    public function getLatestTimes(int $limit = 1000, int $index = 0): array
    {
        $latestTime = Carbon::createFromTimestamp($this->getLatestTime($index));
        $latestTimes = [$latestTime->getTimestamp()];
        switch ($this->getUnit()) {
            case 'm':
                while (--$limit > 0) {
                    array_unshift($latestTimes, $latestTime->subMinutes($this->getNumber())->getTimestamp());
                }
                break;
            case 'h':
                while (--$limit > 0) {
                    array_unshift($latestTimes, $latestTime->subHours($this->getNumber())->getTimestamp());
                }
                break;
            case 'd':
                while (--$limit > 0) {
                    array_unshift($latestTimes, $latestTime->subDays($this->getNumber())->getTimestamp());
                }
                break;
            case 'w':
                while (--$limit > 0) {
                    array_unshift($latestTimes, $latestTime->subDays($this->getNumber() * 7)->getTimestamp());
                }
                break;
            case 'M':
                while (--$limit > 0) {
                    array_unshift($latestTimes, $latestTime->subMonths($this->getNumber())->getTimestamp());
                }
                break;
            default :
                throw new InvalidArgumentException('Interval was not supported.');
        }
        return $latestTimes;
    }

    /**
     * @param int $limit
     * @return int[]
     */
    public function getPreviousLatestTimes(int $limit = 999): array
    {
        return $this->getLatestTimes($limit, -1);
    }

    public function __toString(): string
    {
        return $this->interval;
    }
}
