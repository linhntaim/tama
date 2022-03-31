<?php

/**
 * Base
 */

namespace App\Support\Client;

use Carbon\Carbon;

class DateTimer
{
    public const DATABASE_FORMAT_DATE = 'Y-m-d';
    public const DATABASE_FORMAT_TIME = 'H:i:s';
    public const DATABASE_FORMAT = DateTimer::DATABASE_FORMAT_DATE . ' ' . DateTimer::DATABASE_FORMAT_TIME;

    protected static Carbon $now;

    public static function now(bool $reset = false): Carbon
    {
        if (is_null(static::$now) || $reset) {
            static::$now = Carbon::now(); // timezone = UTC
        }
        return clone static::$now;
    }

    public static function databaseNow(bool $reset = false): string
    {
        return static::now($reset)->format(static::DATABASE_FORMAT, $reset);
    }
}
