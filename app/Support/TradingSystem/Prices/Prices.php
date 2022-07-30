<?php

namespace App\Support\TradingSystem\Prices;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use InvalidArgumentException;

class Prices
{
    protected array $times;

    public function __construct(
        protected array        $data,
        string                 $interval,
        Carbon|string|int|null $lastAt = null)
    {
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
        for ($i = 0, $count = count($data); $i < $count; ++$i) {
            $this->times[] = (clone $lastAt)
                ->{$method}($intervalValue * ($count - $i - 1))
                ->format('Y-m-d H:i:s');
        }
    }

    /**
     * @return array
     */
    public function getPrices(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getTimes(): array
    {
        return $this->times;
    }
}
