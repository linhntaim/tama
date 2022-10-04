<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Data\Analysis;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Data\IndicationMetaItem;
use App\Trading\Bots\Data\Signal;
use App\Trading\Bots\Data\Values;
use App\Trading\Trader;
use Illuminate\Support\Collection;

/**
 * @method Values[]|false getConvertedValues(Packet $packet)
 */
class StochRsiComponent extends Component
{
    public const NAME = 'stoch_rsi';
    protected const DEFAULT_TIME_PERIOD = 14;
    protected const DEFAULT_K_PERIOD = 3;
    protected const DEFAULT_D_PERIOD = 3;
    protected const DEFAULT_LOWER_BAND = 20;
    protected const DEFAULT_UPPER_BAND = 80;

    protected int $timePeriod;

    protected int $kPeriod;

    protected int $dPeriod;

    protected float $lowerBand;

    protected float $upperBand;

    public function timePeriod(): int
    {
        return $this->timePeriod ?? $this->timePeriod = $this->options['time_period'] ?? self::DEFAULT_TIME_PERIOD;
    }

    public function kPeriod(): int
    {
        return $this->kPeriod ?? $this->kPeriod = $this->options['k_period'] ?? self::DEFAULT_K_PERIOD;
    }

    public function dPeriod(): int
    {
        return $this->dPeriod ?? $this->dPeriod = $this->options['d_period'] ?? self::DEFAULT_D_PERIOD;
    }

    public function lowerBand(): float
    {
        return $this->lowerBand ?? $this->lowerBand = $this->options['lower_band'] ?? self::DEFAULT_LOWER_BAND;
    }

    public function upperBand(): float
    {
        return $this->upperBand ?? $this->upperBand = $this->options['upper_band'] ?? self::DEFAULT_UPPER_BAND;
    }

    public function options(): array
    {
        return [
            'time_period' => $this->timePeriod(),
            'k_period' => $this->kPeriod(),
            'd_period' => $this->dPeriod(),
            'lower_band' => $this->lowerBand(),
            'upper_band' => $this->upperBand(),
        ];
    }

    protected function converted(Packet $packet): array|false
    {
        $rsi = Trader::rsi($this->getPrices($packet)->prices(), 14);
        if ($rsi === false) {
            return false;
        }

        $stochRsi = Trader::stoch(
            $rsi,
            $rsi,
            $rsi,
            14,
            3,
            Trader::constant(Trader::MA_TYPE_SMA),
            3,
            Trader::constant(Trader::MA_TYPE_SMA)
        );
        if ($stochRsi === false) {
            return false;
        }

        [$kRsi, $dRsi] = $stochRsi;
        $k = $d = [];
        foreach (array_keys($kRsi) as $index) {
            $k[$index + $this->timePeriod()] = $kRsi[$index];
            $d[$index + $this->timePeriod()] = $dRsi[$index];
        }
        return [
            new Values($k), // K values
            new Values($d), // D values
        ];
    }

    protected function analyzed(Packet $packet, bool|int $latest = true): Collection
    {
        $data = [];
        $stochRsiValues = $this->getConvertedValues($packet);
        if ($stochRsiValues !== false) {
            [$kValues, $dValues] = $stochRsiValues;
            $limit = is_int($latest) ? max(0, $latest) : -1;
            $dataCount = 0;
            $priceCollection = $this->getPrices($packet);
            $priceValues = $priceCollection->prices();
            $timeValues = $priceCollection->times();
            $i = $priceCollection->count();
            $minIndex = $this->timePeriod() + $this->timePeriod() + max($this->kPeriod(), $this->dPeriod()) + 1;
            while (--$i >= 0) {
                if ($i < $minIndex) {
                    break;
                }

                if (($signals = $this->createSignals($timeValues, $priceValues, $kValues, $dValues, $i - 1))->count()) {
                    $data[$i] = $this->createAnalysis(
                        $timeValues[$i],
                        $priceValues[$i],
                        $kValues->value($i),
                        $dValues->value($i),
                        $signals
                    );
                    if (++$dataCount === $limit) {
                        break;
                    }
                }
                if ($latest === true) {
                    break;
                }
            }
        }

        return collect($data);
    }

    protected function createStochRsi(string $k, string $d): array
    {
        return [
            'k' => $k,
            'd' => $d,
            'k_band' => match (true) {
                num_lt($k, $this->lowerBand()) => 'lower',
                num_lt($k, $this->upperBand()) => 'middle',
                default => 'upper'
            },
            'd_band' => match (true) {
                num_lt($d, $this->lowerBand()) => 'lower',
                num_lt($d, $this->upperBand()) => 'middle',
                default => 'upper'
            },
        ];
    }

    protected function createAnalysis(int $time, string $price, string $k, string $d, Collection $signals): Analysis
    {
        return new Analysis($time, $price, $signals, [
            'stoch_rsi' => $this->createStochRsi($k, $d),
        ]);
    }

    /**
     * @param int[] $timeValues
     * @param string[] $priceValues
     * @param Values $kValues
     * @param Values $dValues
     * @param int $index
     * @return Collection
     */
    protected function createSignals(array $timeValues, array $priceValues, Values $kValues, Values $dValues, int $index): Collection
    {
        $signals = collect([]);
        switch (true) {
            case $kValues->isTrough($index): // bottom
                if (num_lt($d2KValue = $kValues->value($index), $this->lowerBand())) {
                    $d2DValue = $dValues->value($index);
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $lowestKValue = $d2KValue;
                    $j = $jMiddle = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jKValue = $kValues->value($j);
                        $jDValue = $dValues->value($j);
                        if (num_gte($jKValue, $this->lowerBand())) {
                            break;
                        }
                        if ($kValues->isNone($j)) {
                            continue;
                        }
                        if ($kValues->isPeak($j)) {
                            if (num_gt($kValues->value($j), $kValues->value($jMiddle))) {
                                $jMiddle = $j;
                            }
                            continue;
                        }
                        if ($kValues->isTrough($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if (num_gt($jKValue, num_min($d2KValue, $lowestKValue))) {
                                if (num_gt($jKValue, $d2KValue) && num_lt($jPriceValue, $d2PriceValue)) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bullish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jKValue, $jDValue,
                                            $d2TimeValue, $d2PriceValue, $d2KValue, $d2DValue,
                                            $timeValues[$jMiddle], $priceValues[$jMiddle], $kValues->value($jMiddle), $dValues->value($jMiddle),
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if (num_eq($jKValue, $d2KValue) && num_eq($jPriceValue, $d2PriceValue)) {
                                continue;
                            }
                            if (num_gte($jPriceValue, $d2PriceValue)) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bullish_divergence',
                                        match (true) {
                                            num_eq($jKValue, $d2KValue) => 'weak',
                                            num_eq($jPriceValue, $d2PriceValue) => 'medium',
                                            default => 'strong'
                                        },
                                        $jTimeValue, $jPriceValue, $jKValue, $jDValue,
                                        $d2TimeValue, $d2PriceValue, $d2KValue, $d2DValue,
                                        $timeValues[$jMiddle], $priceValues[$jMiddle], $kValues->value($jMiddle), $dValues->value($jMiddle),
                                    )
                                );
                                break;
                            }
                            if (num_lt($jKValue, $lowestKValue)) {
                                $lowestKValue = $jKValue;
                            }
                        }
                    }
                }
                break;
            case $kValues->isPeak($index): // top
                if (num_gte($d2KValue = $kValues->value($index), $this->lowerBand())) {
                    $d2DValue = $dValues->value($index);
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $highestKValue = $d2KValue;
                    $j = $jMiddle = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jKValue = $kValues->value($j);
                        $jDValue = $dValues->value($j);
                        if (num_lte($jKValue, $this->lowerBand())) {
                            break;
                        }
                        if ($kValues->isNone($j)) {
                            continue;
                        }
                        if ($kValues->isTrough($j)) {
                            if (num_lt($kValues->value($j), $kValues->value($jMiddle))) {
                                $jMiddle = $j;
                            }
                            continue;
                        }
                        if ($kValues->isPeak($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if (num_lt($jKValue, num_max($d2KValue, $highestKValue))) {
                                if (num_lt($jKValue, $d2KValue) && num_gt($jPriceValue, $d2PriceValue)) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bearish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jKValue, $jDValue,
                                            $d2TimeValue, $d2PriceValue, $d2KValue, $d2DValue,
                                            $timeValues[$jMiddle], $priceValues[$jMiddle], $kValues->value($jMiddle), $dValues->value($jMiddle),
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if (num_eq($jKValue, $d2KValue) && num_eq($jPriceValue, $d2PriceValue)) {
                                continue;
                            }
                            if (num_lte($jPriceValue, $d2PriceValue)) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bearish_divergence',
                                        match (true) {
                                            num_eq($jKValue, $d2KValue) => 'weak',
                                            num_eq($jPriceValue, $d2PriceValue) => 'medium',
                                            default => 'strong'
                                        },
                                        $jTimeValue, $jPriceValue, $jKValue, $jDValue,
                                        $d2TimeValue, $d2PriceValue, $d2KValue, $d2DValue,
                                        $timeValues[$jMiddle], $priceValues[$jMiddle], $kValues->value($jMiddle), $dValues->value($jMiddle),
                                    )
                                );
                                break;
                            }
                            if (num_gt($jKValue, $highestKValue)) {
                                $highestKValue = $jKValue;
                            }
                        }
                    }
                }
                break;
        }
        return $signals;
    }

    protected function createDivergenceSignal(
        string $type,
        string $strength,
        int    $time1,
        string $price1,
        string $k1,
        string $d1,
        int    $time2,
        string $price2,
        string $k2,
        string $d2,
        int    $timeMiddle,
        string $priceMiddle,
        string $kMiddle,
        string $dMiddle
    ): Signal
    {
        return new Signal($type, $strength, [
            'divergence_1' => [
                'time' => $time1,
                'price' => $price1,
                'stoch_rsi' => $this->createStochRsi($k1, $d1),
            ],
            'divergence_2' => [
                'time' => $time2,
                'price' => $price2,
                'stoch_rsi' => $this->createStochRsi($k2, $d2),
            ],
            'divergence_m' => [
                'time' => $timeMiddle,
                'price' => $priceMiddle,
                'rsi' => $this->createStochRsi($kMiddle, $dMiddle),
            ],
        ]);
    }

    protected function transformedIndicationValue(Analysis $analysis): float
    {
        // TODO: Need to improve
        foreach ($analysis->getSignals() as $signal) {
            if ($signal->getType() === 'bullish_divergence') {
                return Indication::VALUE_BUY_MAX;
            }
            if ($signal->getType() === 'bearish_divergence') {
                return Indication::VALUE_SELL_MAX;
            }
        }
        return Indication::VALUE_NEUTRAL;
    }

    protected function transformedIndicationMeta(Analysis $analysis): array
    {
        return [
            new IndicationMetaItem('stoch_rsi', $analysis->getSignals(), [
                'stoch_rsi' => $analysis->get('stoch_rsi'),
            ]),
        ];
    }
}