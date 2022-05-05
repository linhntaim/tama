<?php

namespace App\Support\Trading;

class RsiSwingTradeIndicator extends SwingTradeIndicator
{
    protected int $timePeriod = 14;

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

    public function guessBuying(TradingData $data): ?array
    {
        $closeData = $data->getCloseData();
        $closeTimes = $data->getTimes();
        $rsiValues = $this->trader->rsi($closeData, $this->timePeriod);
        $spikes = $this->trader->spikes($rsiValues, null, $this->lowerBand);
        if ($spikes) {
            foreach ($spikes['tops'] as $index => &$value) {
                $value = [
                    'time' => $closeTimes[$index],
                    'rsi' => $value,
                ];
            }
            foreach ($spikes['bottoms'] as $index => &$value) {
                $value = [
                    'time' => $closeTimes[$index],
                    'rsi' => $value,
                ];
            }
            return $spikes;
        }
        return null;
    }
}