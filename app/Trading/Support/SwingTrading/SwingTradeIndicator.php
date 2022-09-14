<?php

namespace App\Trading\Support\SwingTrading;

use App\Trading\Support\Candles;
use App\Trading\Support\Trader;

abstract class SwingTradeIndicator
{
    protected Trader $trader;

    /**
     * @var array|SwingTrade[]
     */
    protected array $possibleBuys;

    public function __construct(Candles $candles)
    {
        $this->trader = new Trader();
        $this->calculate($candles);
    }

    public function getPossibleBuys(): array
    {
        return $this->possibleBuys;
    }

    public function getPossibleBuy(): ?SwingTrade
    {
        return array_reverse($this->possibleBuys)[0] ?? null;
    }

    protected abstract function calculate(Candles $candles): void;

    protected function bullishDivergences(array $prices, array $troughs): array
    {
        $divergences = [];
        $indices = array_keys($troughs);
        for ($i = 0, $loop = count($indices); $i < $loop - 1; ++$i) {
            $iIndex = $indices[$i];
            for ($j = $i + 1; $j < $loop; ++$j) {
                $jIndex = $indices[$j];
                if ($prices[$jIndex] < $prices[$iIndex]
                    && $troughs[$jIndex] > $troughs[$iIndex]
                    && (function () use ($troughs, $indices, $i, $j, $iIndex, $jIndex) {
                        for ($k = $i + 1; $k < $j; ++$k) {
                            if ($troughs[$indices[$k]] <= $troughs[$iIndex] || $troughs[$indices[$k]] <= $troughs[$jIndex]) {
                                return false;
                            }
                        }
                        return true;
                    })()) {
                    $divergences[] = [$iIndex, $jIndex];
                }
            }
        }
        return $divergences;
    }
}
