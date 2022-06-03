<?php

namespace App\Support\Trading\Strategies\Signals;

use InvalidArgumentException;

class SignalFactory
{
    public static array $bullishSignals = [
        RsiBullishSignal::NAME => RsiBullishSignal::class,
    ];

    public static array $bearishSignals = [
        RsiBearishSignal::NAME => RsiBearishSignal::class,
    ];

    public static function createBullishSignal(string $signal): BullishSignal
    {
        if (is_null($class = (self::$bullishSignals[$signal] ?? null))) {
            throw new InvalidArgumentException(sprintf('Bullish signal [%s] was not supported.', $signal));
        }
        return new $class;
    }

    public static function createBearishSignal(string $signal): BearishSignal
    {
        if (is_null($class = (self::$bearishSignals[$signal] ?? null))) {
            throw new InvalidArgumentException(sprintf('Bearish signal [%s] was not supported.', $signal));
        }
        return new $class;
    }
}
