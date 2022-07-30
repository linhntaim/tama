<?php

namespace App\Support\TradingSystem;

use BadMethodCallException;
use Illuminate\Support\Str;

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
 * @method static array rsi(array $real, ?int $timePeriod = null)
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

    public const TRADER_MA_TYPE_SMA = TRADER_MA_TYPE_SMA;
    public const TRADER_MA_TYPE_EMA = TRADER_MA_TYPE_EMA;
    public const TRADER_MA_TYPE_WMA = TRADER_MA_TYPE_WMA;
    public const TRADER_MA_TYPE_DEMA = TRADER_MA_TYPE_DEMA;
    public const TRADER_MA_TYPE_TEMA = TRADER_MA_TYPE_TEMA;
    public const TRADER_MA_TYPE_TRIMA = TRADER_MA_TYPE_TRIMA;
    public const TRADER_MA_TYPE_KAMA = TRADER_MA_TYPE_KAMA;
    public const TRADER_MA_TYPE_MAMA = TRADER_MA_TYPE_MAMA;
    public const TRADER_MA_TYPE_T3 = TRADER_MA_TYPE_T3;

    public static function __callStatic(string $name, array $arguments)
    {
        if (!function_exists($function = 'trader_' . Str::snake($name))) {
            throw new BadMethodCallException('Method does not exist.');
        }
        return call_user_func($function, ...$arguments);
    }
}
