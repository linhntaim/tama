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
        return $packet->set('converters.rsi', Trader::rsi($this->getPrices($packet)->prices(), $this->timePeriod()));
    }

    protected function analyze(Packet $packet, bool|int $latest = true): Packet
    {
        $limit = is_int($latest) ? ($latest <= 0 ? 0 : $latest) : -1;
        $data = [];
        $dataCount = 0;
        $rsiValues = new Values($packet->get('converters.rsi'));
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
                if (++$dataCount == $limit) {
                    break;
                }
            }
            if ($latest === true) {
                break;
            }
        }

        return $packet->set('analyzers.rsi', collect($data));
    }

    protected function createAnalysis(int $timeValue, float $priceValue, float $rsiValue, Collection $signals): Analysis
    {
        return new Analysis($timeValue, $priceValue, $signals, [
            'rsi' => $rsiValue,
            'band' => $rsiValue < $this->lowerBand()
                ? 'lower' : ($rsiValue < $this->middleBand()
                    ? 'high_lower' : ($rsiValue < $this->upperBand()
                        ? 'low_upper' : 'upper')),
        ]);
    }

    /**
     * @param int[] $timeValues
     * @param float[] $priceValues
     * @param Values $rsiValues
     * @param int $index
     * @return Collection
     */
    protected function createSignals(array $timeValues, array $priceValues, Values $rsiValues, int $index): Collection
    {
        $signals = collect([]);
        switch (true) {
            case $rsiValues->isTrough($index): // bottom
                if (($d2RsiValue = $rsiValues->value($index)) < $this->middleBand()) {
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $lowestRsiValue = $d2RsiValue;
                    $j = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jRsiValue = $rsiValues->value($j);
                        if ($jRsiValue >= $this->middleBand()) {
                            break;
                        }
                        if ($rsiValues->isNone($j)) {
                            continue;
                        }
                        if ($rsiValues->isTrough($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if ($jRsiValue > min($d2RsiValue, $lowestRsiValue)) {
                                if ($jRsiValue > $d2RsiValue && $jPriceValue < $d2PriceValue) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bullish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jRsiValue,
                                            $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if ($jRsiValue == $d2RsiValue && $jPriceValue == $d2PriceValue) {
                                continue;
                            }
                            if ($jPriceValue >= $d2PriceValue) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bullish_divergence',
                                        $jRsiValue == $d2RsiValue
                                            ? 'weak'
                                            : ($jPriceValue == $d2PriceValue ? 'medium' : 'strong'),
                                        $jTimeValue, $jPriceValue, $jRsiValue,
                                        $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                    )
                                );
                                break;
                            }
                            if ($jRsiValue < $lowestRsiValue) {
                                $lowestRsiValue = $jRsiValue;
                            }
                        }
                    }
                }
                break;
            case $rsiValues->isPeak($index): // top
                if (($d2RsiValue = $rsiValues->value($index)) >= $this->middleBand()) {
                    $d2TimeValue = $timeValues[$index];
                    $d2PriceValue = $priceValues[$index];

                    $highestRsiValue = $d2RsiValue;
                    $j = $index;
                    while (--$j >= $this->timePeriod()) {
                        $jRsiValue = $rsiValues->value($j);
                        if ($jRsiValue <= $this->lowerBand()) {
                            break;
                        }
                        if ($rsiValues->isNone($j)) {
                            continue;
                        }
                        if ($rsiValues->isPeak($j)) {
                            $jTimeValue = $timeValues[$j];
                            $jPriceValue = $priceValues[$j];
                            if ($jRsiValue < max($d2RsiValue, $highestRsiValue)) {
                                if ($jRsiValue < $d2RsiValue && $jPriceValue > $d2PriceValue) {
                                    $signals->push(
                                        $this->createDivergenceSignal(
                                            'bearish_divergence', 'hidden',
                                            $jTimeValue, $jPriceValue, $jRsiValue,
                                            $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                        )
                                    );
                                    break;
                                }
                                continue;
                            }
                            if ($jRsiValue == $d2RsiValue && $jPriceValue == $d2PriceValue) {
                                continue;
                            }
                            if ($jPriceValue <= $d2PriceValue) {
                                $signals->push(
                                    $this->createDivergenceSignal(
                                        'bearish_divergence',
                                        $jRsiValue == $d2RsiValue
                                            ? 'weak'
                                            : ($jPriceValue == $d2PriceValue ? 'medium' : 'strong'),
                                        $jTimeValue, $jPriceValue, $jRsiValue,
                                        $d2TimeValue, $d2PriceValue, $d2RsiValue,
                                    )
                                );
                                break;
                            }
                            if ($jRsiValue > $highestRsiValue) {
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
        float  $price1,
        float  $rsi1,
        int    $time2,
        float  $price2,
        float  $rsi2
    ): Signal
    {
        return new Signal($type, $strength, [
            'divergence_1' => [
                'time' => $time1,
                'price' => $price1,
                'rsi' => $rsi1,
            ],
            'divergence_2' => [
                'time' => $time2,
                'price' => $price2,
                'rsi' => $rsi2,
            ],
        ]);
    }

    protected function transform(Packet $packet): Packet
    {
        $priceCollection = $this->getPrices($packet);
        $latestTime = $priceCollection->latestTime();
        return $packet->set(
            'transformers.rsi',
            $packet->get('analyzers.rsi')
                ->map(function (Analysis $analysis, $index) use ($priceCollection, $latestTime) {
                    return new Indication(
                        $value = $analysis->hasSignal('bearish_divergence')
                            ? 1
                            : ($analysis->hasSignal('bullish_divergence')
                                ? -1 : 0),
                        $time = $analysis->getTime(),
                        $analysis->getPrice(),
                        $priceCollection->timeAt($index + 1),
                        $time == $latestTime,
                        $value != 0 ? [
                            new IndicationMetaItem('rsi', $analysis->getSignals(), [
                                'rsi' => $analysis->get('rsi'),
                            ]),
                        ] : []
                    );
                })
        );
    }
}
