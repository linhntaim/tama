<?php

namespace App\Support\Trading\SwingTrading;

use App\Support\Trading\Candles;

class RsiSwingTradeIndicator extends SwingTradeIndicator
{
    protected int $timePeriod = 14;

    protected int $upperBand = 70;

    protected int $middleBand = 50;

    protected int $lowerBand = 30;

    public function setTimePeriod(int $timePeriod): static
    {
        $this->timePeriod = $timePeriod;
        return $this;
    }

    public function setLowerBand(int $lowerBand): static
    {
        $this->lowerBand = $lowerBand;
        return $this;
    }

    protected function calculate(Candles $candles): void
    {
        $times = $candles->getTimes();
        $closes = $candles->getCloses();
        $rsi = $this->trader->rsi($closes, $this->timePeriod);
        $rsiIndices = array_keys($rsi);
        $troughs = $this->trader->troughs($rsi);
        $troughIndices = array_keys($troughs);

        $this->possibleBuys = [];
        foreach ($this->bullishDivergences($closes, $troughs) as $divergenceIndices) {
            [$index, $strength] = $this->findBuy($rsi, $rsiIndices, $troughs, $troughIndices, ...$divergenceIndices);
            $divergenceIndices['buy'] = $index;
            $this->possibleBuys[] = new RsiSwingTrade(
                $index ? $times[$index] : null,
                $index ? $closes[$index] : null,
                $strength,
                new RsiValue($times[$divergenceIndices[0]], $closes[$divergenceIndices[0]], $rsi[$divergenceIndices[0]]),
                new RsiValue($times[$divergenceIndices[1]], $closes[$divergenceIndices[1]], $rsi[$divergenceIndices[1]]),
            );
        }
    }

    protected function findBuy($rsi, $rsiIndices, $troughs, $troughIndices, $indexDivergence1, $indexDivergence2): array
    {
        $findTop = function ($indexRsi1, $indexRsi2) use ($rsi, $rsiIndices) {
            $iRsi1 = array_search($indexRsi1, $rsiIndices);
            $iRsi2 = array_search($indexRsi2, $rsiIndices);
            $top = $rsi[$rsiIndices[$iRsi1 + 1]];
            for ($i = $iRsi1 + 2; $i < $iRsi2; ++$i) {
                if (($t = $rsi[$rsiIndices[$i]]) >= $top) {
                    $top = $t;
                }
            }
            return $top;
        };

        $divergence1 = $rsi[$indexDivergence1];
        $divergence2 = $rsi[$indexDivergence2];
        $divergenceTop = $findTop($indexDivergence1, $indexDivergence2);

        $maxStrength = 10;
        $strength = 0;
        if ($divergence2 < $this->lowerBand) {
            $strength = 5;
        }
        elseif ($divergence1 < $this->lowerBand) {
            $strength = 4;
        }
        elseif ($divergence2 < $this->middleBand) {
            $strength = 3;
        }
        elseif ($divergence1 < $this->middleBand) {
            $strength = 2;
        }
        elseif ($divergence2 < $this->upperBand) {
            $strength = 1;
        }
        if ($strength > 0) {
            if ($divergenceTop < $this->lowerBand) {
                $strength += 5;
            }
            elseif ($divergenceTop < $this->middleBand) {
                $strength += 3;
            }
            elseif ($divergenceTop < $this->upperBand) {
                $strength += 1;
            }
        }
        if ($strength > 0) {
            if (($iTrough = array_search($indexDivergence2, $troughIndices)) !== false) {
                while ($troughIndex = ($troughIndices[++$iTrough] ?? null)) {
                    $trough = $troughs[$troughIndex];
                    if ($trough <= $divergence2 || $trough >= $this->middleBand) {
                        break;
                    }
                    $troughTop = $findTop($indexDivergence2, $troughIndex);
                    if ($troughTop >= $this->middleBand) {
                        break;
                    }
                    if ($troughTop > $divergenceTop) {
                        $decreaseRate = ($troughTop - $trough) / ($troughTop - $divergence2);
                        if ($decreaseRate >= 0.382 && $decreaseRate <= 0.618) {
                            return [$troughIndex, round($strength / $maxStrength, 2)];
                        }
                    }
                }
            }
        }
        return [null, round($strength / $maxStrength, 2)];
    }
}