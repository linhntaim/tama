<?php

namespace App\Trading\Support;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use InvalidArgumentException;

class Candles
{
    /**
     * @var array|Candle[]
     */
    protected array $candles;

    /**
     * @var array|string[]
     */
    protected array $times;

    /**
     * @param array|Candle[] $data
     * @param string $interval
     * @param string|int|null $lastAt
     */
    public function __construct(array $data, string $interval, string|int|null $lastAt = null)
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
        $this->candles = $data;
        for ($i = 0, $count = count($data); $i < $count; ++$i) {
            $this->times[] = (clone $lastAt)
                ->{$method}($intervalValue * ($count - $i - 1))
                ->format('Y-m-d H:i:s');
        }
    }

    public function getCloses(): array
    {
        return collect($this->candles)
            ->map(function (Candle $data) {
                return $data->getClose();
            })
            ->all();
    }

    public function getTimes(): array
    {
        return $this->times;
    }
}
