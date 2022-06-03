<?php

namespace App\Support\Trading\Strategies\DataServices;

use InvalidArgumentException;

class DataServiceFactory
{
    public static array $services = [
        BinanceDataService::NAME => BinanceDataService::class,
    ];

    public static function create(string $service): DataService
    {
        if (is_null($class = (self::$services[$service] ?? null))) {
            throw new InvalidArgumentException(sprintf('Service [%s] was not supported.', $service));
        }
        return new $class;
    }
}
