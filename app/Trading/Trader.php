<?php

namespace App\Trading;

use BadMethodCallException;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @see https://www.php.net/manual/en/function.trader-ema.php
 * @method static array ema(array $real, ?int $timePeriod = null)
 *
 * @see https://www.php.net/manual/en/function.trader-ma.php
 * @method static array ma(array $real, ?int $timePeriod = null, ?int $mAType = null)
 *
 * @see https://www.php.net/manual/en/function.trader-macd.php
 * @method static array macd(array $real, ?int $fastPeriod = null, ?int $slowPeriod = null, ?int $signalPeriod = null)
 *
 * @see https://www.php.net/manual/en/function.trader-rsi.php
 * @method static array|false rsi(array $real, ?int $timePeriod = null)
 *
 * @see https://www.php.net/manual/en/function.trader-stoch.php
 * @method static array|false stoch(array $high, array $low, array $close, ?int $fastK_Period = null, ?int $slowK_Period = null, ?int $slowK_MAType = null, ?int $slowD_Period = null, ?int $slowD_MAType = null)
 *
 * @see https://www.php.net/manual/en/function.trader-stochrsi.php
 * @method static array stochrsi(array $real, ?int $timePeriod = null, ?int $fastKPeriod = null, ?int $fastDPeriod = null, ?int $fastDMAType = null)
 */
class Trader
{
    public const INTERVAL_1_MINUTE = '1m';
    public const INTERVAL_3_MINUTES = '3m';
    public const INTERVAL_5_MINUTES = '5m';
    public const INTERVAL_15_MINUTES = '15m';
    public const INTERVAL_30_MINUTES = '30m';
    public const INTERVAL_1_HOUR = '1h';
    public const INTERVAL_2_HOURS = '2h';
    public const INTERVAL_4_HOURS = '4h';
    public const INTERVAL_6_HOURS = '6h';
    public const INTERVAL_8_HOURS = '8h';
    public const INTERVAL_12_HOURS = '12h';
    public const INTERVAL_1_DAY = '1d';
    public const INTERVAL_3_DAYS = '3d';
    public const INTERVAL_1_WEEK = '1w';
    public const INTERVAL_1_MONTH = '1M';
    public const MA_TYPE_SMA = 'MA_TYPE_SMA';
    public const MA_TYPE_EMA = 'MA_TYPE_EMA';
    public const MA_TYPE_WMA = 'MA_TYPE_WMA';
    public const MA_TYPE_DEMA = 'MA_TYPE_DEMA';
    public const MA_TYPE_TEMA = 'MA_TYPE_TEMA';
    public const MA_TYPE_TRIMA = 'MA_TYPE_TRIMA';
    public const MA_TYPE_KAMA = 'MA_TYPE_KAMA';
    public const MA_TYPE_MAMA = 'MA_TYPE_MAMA';
    public const MA_TYPE_T3 = 'MA_TYPE_T3';

    public static function constant(string $name)
    {
        $name = 'TRADER_' . strtoupper($name);
        if (!defined($name)) {
            throw new InvalidArgumentException('Constant does not exist.');
        }
        return constant($name);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (!function_exists($function = 'trader_' . Str::snake($name))) {
            throw new BadMethodCallException('Method does not exist.');
        }
        return $function(...$arguments);
    }
}
