<?php

namespace App\Trading\Bots\Tests\Data;

use Illuminate\Support\Collection;

class ResultTest
{
    public string $beforePrice;

    public string $beforeBaseAmount;

    public string $beforeQuoteAmount;

    public string $beforeQuoteAmountEquivalent;

    public string $afterPrice;

    public string $afterBaseAmount;

    public string $afterQuoteAmount;

    public string $afterQuoteAmountEquivalent;

    public string $profit;

    public string $profitPercent;

    public string $shownProfitPercent;

    public string $shownStartTime;

    public string $shownEndTime;

    public Collection $swaps;

    /**
     * @param string $exchange
     * @param string $ticker
     * @param string $baseSymbol
     * @param string $quoteSymbol
     * @param float $buyRisk
     * @param float $sellRisk
     * @param int $startTime
     * @param int $endTime
     * @param Collection $swaps
     */
    public function __construct(
        public string $exchange,
        public string $ticker,
        public string $baseSymbol,
        public string $quoteSymbol,
        public float  $buyRisk,
        public float  $sellRisk,
        public int    $startTime,
        public int    $endTime,
        Collection    $swaps
    )
    {
        $this->afterPrice = $swaps->last()->getPrice();
        $this->afterBaseAmount = with(0, static function (string $amount) use ($swaps): string {
            $swaps->each(function (SwapTest $swap) use (&$amount) {
                $amount = num_add($amount, $swap->getBaseAmount());
            });
            return $amount;
        });
        $this->afterQuoteAmount = with(0, static function (string $amount) use ($swaps): string {
            $swaps->each(function (SwapTest $swap) use (&$amount) {
                $amount = num_add($amount, $swap->getQuoteAmount());
            });
            return $amount;
        });
        $this->afterQuoteAmountEquivalent = num_add(num_mul($this->afterPrice, $this->afterBaseAmount), $this->afterQuoteAmount);
        // first swap is initial
        take($swaps->first(), function (SwapTest $swap) {
            $this->beforePrice = $swap->getPrice();
            $this->beforeBaseAmount = $swap->getBaseAmount();
            $this->beforeQuoteAmount = $swap->getQuoteAmount();
            $this->beforeQuoteAmountEquivalent = num_add(num_mul($this->beforePrice, $this->beforeBaseAmount), $this->beforeQuoteAmount);
        });

        $this->profit = num_sub($this->afterQuoteAmountEquivalent, $this->beforeQuoteAmountEquivalent);
        $this->profitPercent = num_mul(num_div($this->profit, $this->beforeQuoteAmountEquivalent), 100, 2);
        $this->shownProfitPercent = sprintf('%s%%', $this->profitPercent);
        $this->shownStartTime = date(DATE_DEFAULT, $this->startTime);
        $this->shownEndTime = date(DATE_DEFAULT, $this->endTime);
        $this->swaps = $swaps;
    }

    public function tradeSwaps(): Collection
    {
        return $this->swaps->filter(fn(SwapTest $swap): bool => num_lt($swap->getQuoteAmount(), 0) || num_lt($swap->getBaseAmount(), 0));
    }

    public function buySwaps(): Collection
    {
        return $this->swaps->filter(fn(SwapTest $swap): bool => num_lt($swap->getQuoteAmount(), 0));
    }

    public function sellSwaps(): Collection
    {
        return $this->swaps->filter(fn(SwapTest $swap): bool => num_lt($swap->getBaseAmount(), 0));
    }
}
