<?php

namespace App\Support\TradingSystem\Analyzer\Oscillators;

use App\Support\TradingSystem\Prices\Prices;
use App\Support\TradingSystem\Trader;

class RsiOscillator extends Oscillator
{
    protected const DEFAULT_TIME_PERIOD = 14;
    protected const DEFAULT_LOWER_BAND = 30;
    protected const DEFAULT_UPPER_BAND = 70;
    protected const DEFAULT_MIDDLE_BAND = 50;

    public function timePeriod(): int
    {
        return $this->options['time_period'] ?? self::DEFAULT_TIME_PERIOD;
    }

    public function lowerBand(): float
    {
        return $this->options['lower_band'] ?? self::DEFAULT_LOWER_BAND;
    }

    public function upperBand(): float
    {
        return $this->options['upper_band'] ?? self::DEFAULT_UPPER_BAND;
    }

    public function middleBand(): float
    {
        return $this->options['middle_band'] ?? self::DEFAULT_MIDDLE_BAND;
    }

    public function signal(Prices $prices, ?array &$history = null): float
    {
        $priceValues = $prices->getPrices();
        $timeValues = $prices->getTimes();
        $rsiValues = Trader::rsi($priceValues, $timePeriod = $this->timePeriod());

        $history = [];
        $loop = $i = count($priceValues);
        $lastHistory = null;
        while (--$i) {
            $index = $loop - $i;
            if (!isset($rsiValues[$index])) {
                $history[$timeValues[$index]] = $lastHistory = 0;
                continue;
            }

            $rsiValue = $rsiValues[$index];
            $calculator = new WeightedValueCalculator();
            if ($rsiValue < 30) {
                $calculator->addValue(-0.5, 1, 'oversold');

                if (($index > $timePeriod + 1)
                    && $rsiValues[$index - 1] < $rsiValue
                    && $rsiValues[$index - 2] > $rsiValues[$index - 1]) {
                    $calculator->addValue(-1, 2, 'oversold.reverse');
                }
            }
            if ($rsiValue > 70) {
                $calculator->addValue(1, 1, 'overbought');
            }
            $history[$timeValues[$index]] = $lastHistory = $calculator->value();
        }

        return count($history) ? $lastHistory : 0.0;
    }
}
