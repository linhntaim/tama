<?php

namespace App\Trading\Bots\Tests\Data;

use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Tests\Reports\IReportTest;
use App\Trading\Bots\Tests\Reports\PlainTextReportTest;
use App\Trading\Trader;
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

    protected IReportTest $reporter;

    /**
     * @param string $exchange
     * @param string $ticker
     * @param string $baseSymbol
     * @param string $quoteSymbol
     * @param float $buyRisk
     * @param float $sellRisk
     * @param int $startTime
     * @param int $endTime
     * @param Collection<int, SwapTest> $swaps
     */
    public function __construct(
        public string     $exchange,
        public string     $ticker,
        public string     $baseSymbol,
        public string     $quoteSymbol,
        public float      $buyRisk,
        public float      $sellRisk,
        public int        $startTime,
        public int        $endTime,
        public Collection $swaps
    )
    {
        $this->afterPrice = Exchanger::connector($this->exchange)
            ->recentPricesAt($this->ticker, new Interval(Trader::INTERVAL_1_MINUTE), $this->endTime, 1)
            ->latestPrice();
        [$this->afterBaseAmount, $this->afterQuoteAmount] = (function () {
            $baseAmount = $quoteAmount = 0;
            $this->swaps->each(function (SwapTest $swap) use (&$baseAmount, &$quoteAmount) {
                $baseAmount = num_add($baseAmount, $swap->getBaseAmount());
                $quoteAmount = num_add($quoteAmount, $swap->getQuoteAmount());
            });
            return [$baseAmount, $quoteAmount];
        })();
        $this->afterQuoteAmountEquivalent = num_add(num_mul($this->afterPrice, $this->afterBaseAmount), $this->afterQuoteAmount);
        // first swap is initial
        take($swaps->first(), function (SwapTest $firstSwap) {
            $this->beforePrice = $firstSwap->getPrice();
            $this->beforeBaseAmount = $firstSwap->getBaseAmount();
            $this->beforeQuoteAmount = $firstSwap->getQuoteAmount();
            $this->beforeQuoteAmountEquivalent = num_add(num_mul($this->beforePrice, $this->beforeBaseAmount), $this->beforeQuoteAmount);
        });

        $this->profit = num_sub($this->afterQuoteAmountEquivalent, $this->beforeQuoteAmountEquivalent);
        $this->profitPercent = num_mul(num_div($this->profit, $this->beforeQuoteAmountEquivalent), 100, 2);
        $this->shownProfitPercent = sprintf('%s%%', $this->profitPercent);
        $this->shownStartTime = date(DATE_DEFAULT, $this->startTime);
        $this->shownEndTime = date(DATE_DEFAULT, $this->endTime);
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

    public function setReporter(IReportTest $reporter): static
    {
        $this->reporter = $reporter;
        return $this;
    }

    public function getReporter(): IReportTest
    {
        return $this->reporter ?? new PlainTextReportTest();
    }

    public function report(): string
    {
        return $this->getReporter()->report($this);
    }
}
