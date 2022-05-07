<?php

namespace App\Support\Trading;

use BadMethodCallException;
use Illuminate\Support\Str;

/**
 * @method array ema(array $real, ?int $timePeriod = null)
 * @method array ma(array $real, ?int $timePeriod = null, ?int $mAType = null)
 * @method array rsi(array $real, ?int $timePeriod = null)
 */
class Trader
{
    public const TRADER_MA_TYPE_SMA = TRADER_MA_TYPE_SMA;
    public const TRADER_MA_TYPE_EMA = TRADER_MA_TYPE_EMA;
    public const TRADER_MA_TYPE_WMA = TRADER_MA_TYPE_WMA;
    public const TRADER_MA_TYPE_DEMA = TRADER_MA_TYPE_DEMA;
    public const TRADER_MA_TYPE_TEMA = TRADER_MA_TYPE_TEMA;
    public const TRADER_MA_TYPE_TRIMA = TRADER_MA_TYPE_TRIMA;
    public const TRADER_MA_TYPE_KAMA = TRADER_MA_TYPE_KAMA;
    public const TRADER_MA_TYPE_MAMA = TRADER_MA_TYPE_MAMA;
    public const TRADER_MA_TYPE_T3 = TRADER_MA_TYPE_T3;

    public function __call(string $name, array $arguments)
    {
        if (!function_exists($function = 'trader_' . Str::snake($name))) {
            throw new BadMethodCallException('Method does not exist.');
        }
        return call_user_func($function, ...$arguments);
    }

    /**
     * @param array $real
     * @param bool|null $top
     * @param int|float|null $low
     * @param int|float|null $high
     * @return array|Peak[]
     */
    public function peaksAndTroughs(array $real, ?bool $top = null, int|float $low = null, int|float $high = null): array
    {
        $peaks = [];
        if (count($real) >= 3) {
            $filter = fn($value) => (is_null($low) || $value >= $low) && (is_null($high) || $value <= $high);
            $indices = array_keys($real);
            $up = $real[$indices[1]] > $real[$indices[0]];
            for ($i = 1, $loop = count($indices); $i < $loop; ++$i) {
                $prevIndex = $indices[$i - 1];
                $prevValue = $real[$prevIndex];
                if ($up) {
                    if ($real[$indices[$i]] < $prevValue) { // top
                        $up = false;
                        if (($top ?? true) && $filter($prevValue)) {
                            $peaks[$prevIndex] = new Peak($prevValue);
                        }
                    }
                }
                else {
                    if ($real[$indices[$i]] > $prevValue) { // bottom
                        $up = true;
                        if (!$top && $filter($prevValue)) {
                            $peaks[$prevIndex] = new Trough($prevValue);
                        }
                    }
                }
            }
        }
        return $peaks;
    }

    /**
     * @param array $real
     * @param int|float|null $low
     * @param int|float|null $high
     * @return array
     */
    public function peaks(array $real, int|float $low = null, int|float $high = null): array
    {
        return array_map(fn(Peak $peak) => $peak->value, $this->peaksAndTroughs($real, true, $low, $high));
    }

    /**
     * @param array $real
     * @param int|float|null $low
     * @param int|float|null $high
     * @return array
     */
    public function troughs(array $real, int|float $low = null, int|float $high = null): array
    {
        return array_map(fn(Peak $peak) => $peak->value, $this->peaksAndTroughs($real, false, $low, $high));
    }
}