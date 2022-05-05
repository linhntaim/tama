<?php

namespace App\Support;

use BadMethodCallException;

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
        if (!function_exists($function = 'trader_' . $name)) {
            throw new BadMethodCallException('Method does not exist.');
        }
        return call_user_func($function, ...$arguments);
    }

    public function spikes(array $real, int|float $lower = null, int|float $upper = null, array $options = []): ?array
    {
        $count = count($real);
        if ($count < 3) {
            return null;
        }
        $latestTops = $options['latest_tops'] ?? 0;
        $latestBottoms = $options['latest_bottoms'] ?? 0;
        $tops = [];
        $bottoms = [];
        $indices = array_keys($real);
        while ($i = array_pop($indices)) {
            if (!isset($prev)) {
                $prev = $real[$i];
                continue;
            }
            if (!isset($down)) {
                $down = $real[$i] < $prev;
                continue;
            }
            if ($down) {
                if ($real[$i] < $prev) {
                    $down = false;
                    $bottoms[$i] = $real[$i];
                }
            }
            else {
                if ($real[$i] > $prev) {
                    $down = true;
                    $tops[$i] = $real[$i];
                }
            }
            if (($latestTops && count($tops) == $latestTops)
                || ($latestBottoms && count($bottoms) == $latestBottoms)) {
                break;
            }
            $prev = $real[$i];
        }
        $filter = fn($value) => (is_null($lower) || $value >= $lower) && (is_null($upper) || $value <= $upper);
        return nullify_empty_array(
            array_filter([
                'tops' => nullify_empty_array(array_filter($tops, $filter)),
                'bottoms' => nullify_empty_array(array_filter($bottoms, $filter)),
            ])
        );
    }
}