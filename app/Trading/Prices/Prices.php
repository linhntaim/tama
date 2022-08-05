<?php

namespace App\Trading\Prices;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use InvalidArgumentException;

class Prices
{
    protected array $times;

    protected string $latestTime;

    protected int $count;

    public function __construct(
        protected string       $ticker,
        protected string       $interval,
        protected array        $data,
        Carbon|string|int|null $lastAt = null)
    {
        $this->count = count($data);
        if (!($lastAt instanceof Carbon)) {
            $lastAt = is_int($lastAt)
                ? Carbon::createFromTimestamp($lastAt, new CarbonTimeZone('UTC'))
                : new Carbon($lastAt ?: 'now', new CarbonTimeZone('UTC'));
        }
        $intervalValue = (int)$interval;
        $timestampLastAt = $lastAt->timestamp + 62135596800;
        switch (substr($interval, -1)) {
            case 'm':
                $lastAt
                    ->subMinutes(((int)($timestampLastAt / 60)) % $intervalValue)
                    ->second(0);
                $method = 'subMinutes';
                break;
            case 'h':
                $lastAt
                    ->subHours(((int)($timestampLastAt / 3600)) % $intervalValue)
                    ->minute(0)
                    ->second(0);
                $method = 'subHours';
                break;
            case 'w':
                $intervalValue *= 7;
            case 'd':
                $lastAt
                    ->subDays(((int)($timestampLastAt / 3600 * 24)) % $intervalValue)
                    ->hour(0)
                    ->minute(0)
                    ->second(0);
                $method = 'subDays';
                break;
            case 'M':
                $lastAt
                    ->subMonths((($lastAt->year - 1) * 12 + $lastAt->month) % $intervalValue)
                    ->day(1)
                    ->hour(0)
                    ->minute(0)
                    ->second(0);
                $method = 'subMonths';
                break;
            default:
                throw new InvalidArgumentException('Interval was not supported.');
        }
        $this->times = [];
        for ($i = 0; $i < $this->count; ++$i) {
            $this->times[] = (clone $lastAt)
                ->{$method}($intervalValue * ($this->count - $i - 1))
                ->format('Y-m-d H:i:s');
        }
        $this->latestTime = $this->times[$this->count - 1];
    }

    public function getTicker(): string
    {
        return $this->ticker;
    }

    public function getInterval(): string
    {
        return $this->interval;
    }

    public function getValues(): array
    {
        return $this->data;
    }

    public function getTimes(): array
    {
        return $this->times;
    }

    public function getLatestTime(): string
    {
        return $this->latestTime;
    }

    public function count(): int
    {
        return $this->count;
    }
}
