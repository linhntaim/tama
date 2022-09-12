<?php

namespace App\Trading\Bots\Oscillators;

use App\Trading\Bots\Data\Analysis;
use App\Trading\Bots\Data\Indication;
use App\Trading\Bots\Data\IndicationMetaItem;
use App\Trading\Bots\Data\Signal;
use App\Trading\Bots\Data\Values;
use App\Trading\Trader;
use Illuminate\Support\Collection;

class RsiComponent extends Component
{
    public const NAME = 'rsi';
    protected const DEFAULT_TIME_PERIOD = 14;
    protected const DEFAULT_LOWER_BAND = 30;
    protected const DEFAULT_UPPER_BAND = 70;
    protected const DEFAULT_MIDDLE_BAND = 50;

    protected int $timePeriod;

    protected float $lowerBand;

    protected float $upperBand;

    protected float $middleBand;

    public function timePeriod(): int
    {
        return $this->timePeriod ?? $this->timePeriod = $this->options['time_period'] ?? self::DEFAULT_TIME_PERIOD;
    }

    public function lowerBand(): float
    {
        return $this->lowerBand ?? $this->lowerBand = $this->options['lower_band'] ?? self::DEFAULT_LOWER_BAND;
    }

    public function middleBand(): float
    {
        return $this->middleBand ?? $this->middleBand = $this->options['middle_band'] ?? self::DEFAULT_MIDDLE_BAND;
    }

    public function upperBand(): float
    {
        return $this->upperBand ?? $this->upperBand = $this->options['upper_band'] ?? self::DEFAULT_UPPER_BAND;
    }

    public function options(): array
    {
        return [
            'time_period' => $this->timePeriod(),
            'lower_band' => $this->lowerBand(),
            'middle_band' => $this->middleBand(),
            'upper_band' => $this->upperBand(),
        ];
    }

    protected function convert(Packet $packet): Packet
    {
        return $packet->set(
            'converters.rsi',
            ($rsi = Trader::rsi($this->getPrices($packet)->prices(), $this->timePeriod())) !== false
                ? new Values($rsi) : false
        );
    }

    protected function getRsiValues(Packet $packet): Values|false
    {
        return $packet->get('converters.rsi');
    }

    protected function analyze(Packet $packet, bool|int $latest = true): Packet
    {
        $data = [];
        $rsiValues = $this->getRsiValues($packet);
        if ($rsiValues !== false) {
            $limit = is_int($latest) ? max(0, $latest) : -1;
            $dataCount = 0;
            $priceCollection = $this->getPrices($packet);
            $priceValues = $priceCollection->prices();
            $timeValues = $priceCollection->times();
            $i = $priceCollection->count();
            while (--$i >= 0) {
                if ($i < $this->timePeriod() + 1) {
                    break;
                }

                if (($signals = $this->createSignals($timeValues, $priceValues, $rsiValues, $i - 1))->count()) {
                    $data[$i] = $this->createAnalysis(
                        $timeValues[$i],
                        $priceValues[$i],
                        $rsiValues->value($i),
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

        return $packet->set('analyzers.rsi', collect($data));
    }

    protected function createRsi(string $rsi): array
    {
        return [
            'value' => $rsi,
            'band' => match (true) {
                num_lt($rsi, $this->lowerBand()) => 'lower',
                num_lt($rsi, $this->middleBand()) => 'high_lower',
                num_lt($rsi, $this->upperBand()) => 'low_upper',
                default => 'upper'
            },
        ];
    }

    protected function createAnalysis(int $time, string $price, string $rsi, Collection $signals): Analysis
    {
        return new Analysis($time, $price, $signals, [
            'rsi' => $this->createRsi($rsi),
        ]);
    }

    /**
     * @param int[] $timeValues
     * @param string[] $priceValues
     * @param Values $rsiValues
     * @param int $index
     * @return Collection
     */
    protected function createSignals(array $timeValues, array $priceValues, Values $rsiValues, int $index): Collection
    {
        $signals = collect([]);
        switch (true) {
            case $rsiValues->isTrough($index): // bottom
                if (num_lt($d2RsiValue = $rsiValues->value($index), $this->middleBand())) {
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $lowestRsiValue = $d2RsiValue;
                    $j = $jMiddle = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jRsiValue = $rsiValues->value($j);
                        if (num_gte($jRsiValue, $this->middleBand())) {
                            break;
                        }
                        if ($rsiValues->isNone($j)) {
                            continue;
                        }
                        if ($rsiValues->isPeak($j)) {
                            if (num_gt($rsiValues->value($j), $rsiValues->value($jMiddle))) {
                                $jMiddle = $j;
                            }
                            continue;
                        }
                        if ($rsiValues->isTrough($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if (num_gt($jRsiValue, num_min($d2RsiValue, $lowestRsiValue))) {
                                if (num_gt($jRsiValue, $d2RsiValue) && num_lt($jPriceValue, $d2PriceValue)) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bullish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jRsiValue,
                                            $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                            $timeValues[$jMiddle], $priceValues[$jMiddle], $rsiValues->value($jMiddle),
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if (num_eq($jRsiValue, $d2RsiValue) && num_eq($jPriceValue, $d2PriceValue)) {
                                continue;
                            }
                            if (num_gte($jPriceValue, $d2PriceValue)) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bullish_divergence',
                                        match (true) {
                                            num_eq($jRsiValue, $d2RsiValue) => 'weak',
                                            num_eq($jPriceValue, $d2PriceValue) => 'medium',
                                            default => 'strong'
                                        },
                                        $jTimeValue, $jPriceValue, $jRsiValue,
                                        $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                        $timeValues[$jMiddle], $priceValues[$jMiddle], $rsiValues->value($jMiddle),
                                    )
                                );
                                break;
                            }
                            if (num_lt($jRsiValue, $lowestRsiValue)) {
                                $lowestRsiValue = $jRsiValue;
                            }
                        }
                    }
                }
                break;
            case $rsiValues->isPeak($index): // top
                if (num_gte($d2RsiValue = $rsiValues->value($index), $this->middleBand())) {
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $highestRsiValue = $d2RsiValue;
                    $j = $jMiddle = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jRsiValue = $rsiValues->value($j);
                        if (num_lte($jRsiValue, $this->lowerBand())) {
                            break;
                        }
                        if ($rsiValues->isNone($j)) {
                            continue;
                        }
                        if ($rsiValues->isTrough($j)) {
                            if (num_lt($rsiValues->value($j), $rsiValues->value($jMiddle))) {
                                $jMiddle = $j;
                            }
                            continue;
                        }
                        if ($rsiValues->isPeak($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if (num_lt($jRsiValue, num_max($d2RsiValue, $highestRsiValue))) {
                                if (num_lt($jRsiValue, $d2RsiValue) && num_gt($jPriceValue, $d2PriceValue)) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bearish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jRsiValue,
                                            $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                            $timeValues[$jMiddle], $priceValues[$jMiddle], $rsiValues->value($jMiddle),
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if (num_eq($jRsiValue, $d2RsiValue) && num_eq($jPriceValue, $d2PriceValue)) {
                                continue;
                            }
                            if (num_lte($jPriceValue, $d2PriceValue)) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bearish_divergence',
                                        match (true) {
                                            num_eq($jRsiValue, $d2RsiValue) => 'weak',
                                            num_eq($jPriceValue, $d2PriceValue) => 'medium',
                                            default => 'strong'
                                        },
                                        $jTimeValue, $jPriceValue, $jRsiValue,
                                        $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                        $timeValues[$jMiddle], $priceValues[$jMiddle], $rsiValues->value($jMiddle),
                                    )
                                );
                                break;
                            }
                            if (num_gt($jRsiValue, $highestRsiValue)) {
                                $highestRsiValue = $jRsiValue;
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
        string $rsi1,
        int    $time2,
        string $price2,
        string $rsi2,
        int    $timeMiddle,
        string $priceMiddle,
        string $rsiMiddle
    ): Signal
    {
        return new Signal($type, $strength, [
            'divergence_1' => [
                'time' => $time1,
                'price' => $price1,
                'rsi' => $this->createRsi($rsi1),
            ],
            'divergence_2' => [
                'time' => $time2,
                'price' => $price2,
                'rsi' => $this->createRsi($rsi2),
            ],
            'divergence_m' => [
                'time' => $timeMiddle,
                'price' => $priceMiddle,
                'rsi' => $this->createRsi($rsiMiddle),
            ],
        ]);
    }

    protected function transform(Packet $packet): Packet
    {
        return $packet->set(
            'transformers.rsi',
            $packet->get('analyzers.rsi')
                ->map(function (Analysis $analysis) {
                    return new Indication(
                        match (true) {
                            $analysis->hasSignal('bullish_divergence') => Indication::VALUE_BUY_MAX,
                            $analysis->hasSignal('bearish_divergence') => Indication::VALUE_SELL_MAX,
                            default => Indication::VALUE_NEUTRAL
                        },
                        $analysis->getTime(),
                        $analysis->getPrice(),
                        [
                            new IndicationMetaItem('rsi', $analysis->getSignals(), [
                                'rsi' => $analysis->get('rsi'),
                            ]),
                        ]
                    );
                })
        );
    }
}
