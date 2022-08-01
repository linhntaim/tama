<?php

namespace App\Support\TradingSystem\Bots\Oscillators;

use App\Support\TradingSystem\Trader;
use Illuminate\Support\Collection;

class RsiComponent extends Component
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

    protected function convert(Packet $packet): Packet
    {
        return $packet->set('converters.rsi', Trader::rsi($this->getPrices($packet)->getValues(), $this->timePeriod()));
    }

    protected function analyze(Packet $packet): Packet
    {
        $data = [];
        $rsiValues = $packet->get('converters.rsi');
        $rsiTimePeriod = $this->timePeriod();
        $prices = $this->getPrices($packet);
        $priceValues = $prices->getValues();
        $priceTimes = $prices->getTimes();
        $loop = $i = $this->getPrices($packet)->count();
        while (--$i >= 0) {
            $index = $loop - $i - 1;

            $timeValue = $priceTimes[$index];
            $rsiValue = $rsiValues[$index] ?? null;

            if (is_null($rsiValue)) {
                $data[] = [
                    'time' => $timeValue,
                    'price' => $priceValues[$index],
                    'rsi' => null,
                ];
                continue;
            }

            $start = $index == $rsiTimePeriod;
            $end = $i == 0;
            $prevIndex = $index - 1;
            $prevTimeValue = $priceTimes[$prevIndex];

            $data[] = [
                'time' => $timeValue,
                'price' => $priceValues[$index],
                'rsi' => $rsiValue,
                'trend' => !$start
                    ? ($rsiValue == $rsiValues[$prevIndex]
                        ? $data[$prevIndex]['trend']
                        : ($rsiValue > $rsiValues[$prevIndex] ? 'up' : 'down'))
                    : null,
                'high' => !$start ? 'middle' : null,
                'position' => !$start ? (!$end ? 'middle' : 'end') : 'start',

                'band' => $rsiValue < $this->lowerBand()
                    ? 'lower' : ($rsiValue < $this->middleBand()
                        ? 'high_lower' : ($rsiValue < $this->upperBand()
                            ? 'low_upper' : 'upper')),

                'trades' => [],
            ];

            if (!$start && !$end) {
                // top & bottom
                if (($trend = $data[$index]['trend']) != $data[$prevIndex]['trend']) {
                    $data[$prevIndex]['high'] = $trend == 'up' ? 'bottom' : 'top';
                }
                // bullish divergence
                switch ($data[$prevIndex]['high']) {
                    case 'bottom':
                        if (($d2RsiValue = $data[$prevIndex]['rsi']) < $this->middleBand()) {
                            $d2TimeValue = $prevTimeValue;
                            $d2PriceValue = $priceValues[$prevIndex];

                            $lowestRsiValue = $d2RsiValue;
                            $j = $prevIndex;
                            while (--$j >= $rsiTimePeriod) {
                                $jTimeValue = $priceTimes[$j];
                                $jHigh = $data[$j]['high'];

                                if ($jHigh == 'middle') {
                                    continue;
                                }

                                $jRsiValue = $data[$j]['rsi'];
                                if ($jRsiValue >= $this->middleBand()) {
                                    break;
                                }
                                if ($jHigh == 'bottom') {
                                    $jPriceValue = $priceValues[$j];
                                    if ($jRsiValue > min($d2RsiValue, $lowestRsiValue)) {
                                        if ($jRsiValue > $d2RsiValue && $jPriceValue < $d2PriceValue) {
                                            $data[$index]['trades'][] = [
                                                'type' => 'bullish_divergence',
                                                'strength' => 'hidden',
                                                'divergence_1' => [
                                                    'time' => $jTimeValue,
                                                    'price' => $jPriceValue,
                                                    'rsi' => $jRsiValue,
                                                ],
                                                'divergence_2' => [
                                                    'time' => $d2TimeValue,
                                                    'rsi' => $d2RsiValue,
                                                    'price' => $d2PriceValue,
                                                ],
                                            ];
                                            break;
                                        }
                                        continue;
                                    }
                                    if ($jRsiValue == $d2RsiValue && $jPriceValue == $d2PriceValue) {
                                        continue;
                                    }
                                    if ($jPriceValue >= $d2PriceValue) {
                                        $data[$index]['trades'][] = [
                                            'type' => 'bullish_divergence',
                                            'strength' => $jRsiValue == $d2RsiValue
                                                ? 'weak'
                                                : ($jPriceValue == $d2PriceValue ? 'medium' : 'strong'),
                                            'divergence_1' => [
                                                'time' => $jTimeValue,
                                                'price' => $jPriceValue,
                                                'rsi' => $jRsiValue,
                                            ],
                                            'divergence_2' => [
                                                'time' => $d2TimeValue,
                                                'price' => $d2PriceValue,
                                                'rsi' => $d2RsiValue,
                                            ],
                                        ];
                                        break;
                                    }
                                    if ($jRsiValue < $lowestRsiValue) {
                                        $lowestRsiValue = $jRsiValue;
                                    }
                                }
                            }
                        }
                        break;
                    case 'top':
                        if (($d2RsiValue = $data[$prevIndex]['rsi']) >= $this->middleBand()) {
                            $d2TimeValue = $prevTimeValue;
                            $d2PriceValue = $priceValues[$prevIndex];

                            $highestRsiValue = $d2RsiValue;
                            $j = $prevIndex;
                            while (--$j >= $rsiTimePeriod) {
                                $jTimeValue = $priceTimes[$j];
                                $jHigh = $data[$j]['high'];

                                if ($jHigh == 'middle') {
                                    continue;
                                }

                                $jRsiValue = $data[$j]['rsi'];
                                if ($jRsiValue <= $this->lowerBand()) {
                                    break;
                                }
                                if ($jHigh == 'top') {
                                    $jPriceValue = $priceValues[$j];
                                    if ($jRsiValue < max($d2RsiValue, $highestRsiValue)) {
                                        if ($jRsiValue < $d2RsiValue && $jPriceValue > $d2PriceValue) {
                                            $data[$index]['trades'][] = [
                                                'type' => 'bearish_divergence',
                                                'strength' => 'hidden',
                                                'divergence_1' => [
                                                    'time' => $jTimeValue,
                                                    'price' => $jPriceValue,
                                                    'rsi' => $jRsiValue,
                                                ],
                                                'divergence_2' => [
                                                    'time' => $d2TimeValue,
                                                    'rsi' => $d2RsiValue,
                                                    'price' => $d2PriceValue,
                                                ],
                                            ];
                                            break;
                                        }
                                        continue;
                                    }
                                    if ($jRsiValue == $d2RsiValue && $jPriceValue == $d2PriceValue) {
                                        continue;
                                    }
                                    if ($jPriceValue <= $d2PriceValue) {
                                        $data[$index]['trades'][] = [
                                            'type' => 'bearish_divergence',
                                            'strength' => $jRsiValue == $d2RsiValue
                                                ? 'weak'
                                                : ($jPriceValue == $d2PriceValue ? 'medium' : 'strong'),
                                            'divergence_1' => [
                                                'time' => $jTimeValue,
                                                'price' => $jPriceValue,
                                                'rsi' => $jRsiValue,
                                            ],
                                            'divergence_2' => [
                                                'time' => $d2TimeValue,
                                                'price' => $d2PriceValue,
                                                'rsi' => $d2RsiValue,
                                            ],
                                        ];
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
            }
        }

        return $packet->set('analyzers.rsi', $data);
    }

    protected function transform(Packet $packet): Packet
    {
        return $packet->set(
            'transformers.rsi',
            collect($packet->get('analyzers.rsi'))
                ->map(function ($item) {
                    return [
                        'value' => $value = is_null($item['rsi'])
                            ? 0
                            : (function (Collection $trades) {
                                return $trades->whereIn('type', [
                                    'bearish_divergence',
                                ])->count() > 0
                                    ? 1
                                    : ($trades->whereIn('type', [
                                        'bullish_divergence',
                                    ])->count() > 0 ? -1 : 0);
                            })(collect($item['trades'])),
                        'time' => $item['time'],
                        'price' => $item['price'],
                        'meta' => $value == 0
                            ? null
                            : [
                                [
                                    'type' => 'rsi',
                                    'rsi' => $item['rsi'],
                                    'trades' => $item['trades'],
                                ],
                            ],
                    ];
                })
                ->all()
        );
    }
}
