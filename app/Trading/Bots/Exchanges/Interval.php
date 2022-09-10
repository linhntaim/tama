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

    public function eq(Interval $interval): bool
    {
        return $this->getUnit() === $interval->getUnit()
            && $this->getNumber() === $interval->getNumber();
    }

    public function gt(Interval $interval): bool
    {
        $unitOrder = [
            'm' => 0,
            'h' => 1,
            'd' => 2,
            'w' => 3,
            'M' => 4,
        ];
        return ($thisUnit = $unitOrder[$this->getUnit()]) > ($thatUnit = $unitOrder[$interval->getUnit()])
            || ($thisUnit === $thatUnit && $this->getNumber() > $interval->getNumber());
    }

    public function gte(Interval $interval): bool
    {
        $unitOrder = [
            'm' => 0,
            'h' => 1,
            'd' => 2,
            'w' => 3,
            'M' => 4,
        ];
        return ($thisUnit = $unitOrder[$this->getUnit()]) > ($thatUnit = $unitOrder[$interval->getUnit()])
            || ($thisUnit === $thatUnit && $this->getNumber() >= $interval->getNumber());
    }

    public function lt(Interval $interval): bool
    {
        $unitOrder = [
            'm' => 0,
            'h' => 1,
            'd' => 2,
            'w' => 3,
            'M' => 4,
        ];
        return ($thisUnit = $unitOrder[$this->getUnit()]) < ($thatUnit = $unitOrder[$interval->getUnit()])
            || ($thisUnit === $thatUnit && $this->getNumber() < $interval->getNumber());
    }

    public function lte(Interval $interval): bool
    {
        $unitOrder = [
            'm' => 0,
            'h' => 1,
            'd' => 2,
            'w' => 3,
            'M' => 4,
        ];
        return ($thisUnit = $unitOrder[$this->getUnit()]) < ($thatUnit = $unitOrder[$interval->getUnit()])
            || ($thisUnit === $thatUnit && $this->getNumber() <= $interval->getNumber());
    }

    public function gcd(Interval $interval): int
    {
        $unitOrder = [
            'm' => fn($number) => $number * 60,
            'h' => fn($number) => $number * 3600,
            'd' => fn() => 24 * 3600,
            'w' => fn() => 24 * 3600,
            'M' => fn() => 24 * 3600,
        ];

        return gcd(
            $unitOrder[$this->getUnit()]($this->getNumber()),
            $unitOrder[$interval->getUnit()]($interval->getNumber())
        );
    }

    public function findOpenTimeOf(Carbon|int|null $time = null, int $directionIndex = 0, bool $asInt = true): int|Carbon
    {
        $carbon = is_null($time)
            ? Carbon::now()
            : ($time instanceof Carbon ? $time : Carbon::createFromTimestamp($time));
        $timestamp = $carbon->getTimestamp() + 62135596800; // full timestamp from 01/01/0001 00:00:00
        return with(
            match ($this->getUnit()) {
                'm' => $carbon
                    ->subMinutes(int_floor($timestamp / 60) % $this->getNumber())
                    ->addMinutes($this->getNumber() * $directionIndex)
                    ->second(0),
                'h' => $carbon
                    ->subHours(int_floor($timestamp / 3600) % $this->getNumber())
                    ->addHours($this->getNumber() * $directionIndex)
                    ->minute(0)
                    ->second(0),
                'd' => $carbon
                    ->subDays(int_floor($timestamp / 3600 * 24) % $this->getNumber())
                    ->addDays($this->getNumber() * $directionIndex)
                    ->hour(0)
                    ->minute(0)
                    ->second(0),
                'w' => $carbon
                    ->subDays(int_floor($timestamp / 3600 * 24) % ($this->getNumber() * 7))
                    ->addDays($this->getNumber() * $directionIndex * 7)
                    ->hour(0)
                    ->minute(0)
                    ->second(0),
                'M' => $carbon
                    ->subMonths((($carbon->year - 1) * 12 + $carbon->month) % $this->getNumber())
                    ->addMonths($this->getNumber() * $directionIndex)
                    ->day(1)
                    ->hour(0)
                    ->minute(0)
                    ->second(0),
                default => throw new InvalidArgumentException('Interval was not supported.'),
            },
            static fn(Carbon $time) => $asInt ? $time->getTimestamp() : $time
        );
    }

    public function isExact(int $time): bool
    {
        return $time === $this->findOpenTimeOf($time);
    }

    public function getLatestOpenTime(int $directionIndex = 0, bool $asInt = true): int|Carbon
    {
        return $this->findOpenTimeOf(null, $directionIndex, $asInt);
    }

    public function getPreviousOpenTimeOfLatest(bool $asInt = true): int|Carbon
    {
        return $this->getLatestOpenTime(-1, $asInt);
    }

    public function getPreviousOpenTimeOf(Carbon|int|null $time = null, bool $asInt = true): int|Carbon
    {
        return $this->findOpenTimeOf($time, -1, $asInt);
    }

    public function getPreviousOpenTimeOfExact(int $openTime, int $index = 0): int
    {
        $index = max($index, 0);
        return (match ($this->getUnit()) {
            'm' => Carbon::createFromTimestamp($openTime)->subMinutes($this->getNumber() * ($index + 1)),
            'h' => Carbon::createFromTimestamp($openTime)->subHours($this->getNumber() * ($index + 1)),
            'd' => Carbon::createFromTimestamp($openTime)->subDays($this->getNumber() * ($index + 1)),
            'w' => Carbon::createFromTimestamp($openTime)->subDays($this->getNumber() * ($index + 1) * 7),
            'M' => Carbon::createFromTimestamp($openTime)->subMonths($this->getNumber() * ($index + 1)),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        })->getTimestamp();
    }

    public function getNextOpenTimeOf(Carbon|int|null $time = null, bool $asInt = true): int|Carbon
    {
        return $this->findOpenTimeOf($time, 1, $asInt);
    }

    public function getNextOpenTimeOfExact(int $openTime, int $index = 0): int
    {
        $index = max($index, 0);
        return (match ($this->getUnit()) {
            'm' => Carbon::createFromTimestamp($openTime)->addMinutes($this->getNumber() * ($index + 1)),
            'h' => Carbon::createFromTimestamp($openTime)->addHours($this->getNumber() * ($index + 1)),
            'd' => Carbon::createFromTimestamp($openTime)->addDays($this->getNumber() * ($index + 1)),
            'w' => Carbon::createFromTimestamp($openTime)->addDays($this->getNumber() * ($index + 1) * 7),
            'M' => Carbon::createFromTimestamp($openTime)->addMonths($this->getNumber() * ($index + 1)),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        })->getTimestamp();
    }

    public function diffIndexOfExact(int $openTime1, int $openTime2): int
    {
        return (match ($this->getUnit()) {
            'm' => Carbon::createFromTimestamp($openTime1)->diffInMinutes(Carbon::createFromTimestamp($openTime2)) / $this->getNumber(),
            'h' => Carbon::createFromTimestamp($openTime1)->diffInHours(Carbon::createFromTimestamp($openTime2)) / $this->getNumber(),
            'd' => Carbon::createFromTimestamp($openTime1)->diffInDays(Carbon::createFromTimestamp($openTime2)) / $this->getNumber(),
            'w' => Carbon::createFromTimestamp($openTime1)->diffInWeeks(Carbon::createFromTimestamp($openTime2)) / $this->getNumber(),
            'M' => Carbon::createFromTimestamp($openTime1)->diffInMonths(Carbon::createFromTimestamp($openTime2)) / $this->getNumber(),
            default => throw new InvalidArgumentException('Interval was not supported.'),
        });
    }

    /**
     * @param int $limit
     * @param Carbon|int|null $time
     * @param int $directionIndex
     * @param bool $asInt
     * @return int[]|Carbon[]
     */
    public function getRecentOpenTimes(int $limit, Carbon|int|null $time = null, int $directionIndex = 0, bool $asInt = true): array
    {
        $transform = static fn(Carbon $openTime) => $asInt ? $openTime->getTimestamp() : $openTime;
        $openTime = $this->findOpenTimeOf($time, $directionIndex, false);
        $openTimes = [$transform($openTime)];
        switch ($this->getUnit()) {
            case 'm':
                while (--$limit > 0) {
                    array_unshift($openTimes, $transform($openTime->subMinutes($this->getNumber())));
                }
                break;
            case 'h':
                while (--$limit > 0) {
                    array_unshift($openTimes, $transform($openTime->subHours($this->getNumber())));
                }
                break;
            case 'd':
                while (--$limit > 0) {
                    array_unshift($openTimes, $transform($openTime->subDays($this->getNumber())));
                }
                break;
            case 'w':
                while (--$limit > 0) {
                    array_unshift($openTimes, $transform($openTime->subDays($this->getNumber() * 7)));
                }
                break;
            case 'M':
                while (--$limit > 0) {
                    array_unshift($openTimes, $transform($openTime->subMonths($this->getNumber())));
                }
                break;
            default :
                throw new InvalidArgumentException('Interval was not supported.');
        }
        return $openTimes;
    }

    public function __toString(): string
    {
        return $this->interval;
    }
}
