<?php

namespace App\Trading\Bots\Tests;

use Illuminate\Support\Collection;

class ResultTest
{
    protected float $beforePrice;

    protected float $beforeBaseAmount;

    protected float $beforeQuoteAmount;

    protected float $beforeQuoteAmountEquivalent;

    protected float $afterPrice;

    protected float $afterBaseAmount;

    protected float $afterQuoteAmount;

    protected float $afterQuoteAmountEquivalent;

    protected float $profit;

    protected float $profitPercent;

    protected string $shownProfitPercent;

    protected string $shownStartTime;

    protected string $shownEndTime;

    protected array $trades;

    /**
     * @param string $exchange
     * @param string $ticker
     * @param string $baseSymbol
     * @param string $quoteSymbol
     * @param int $startTime
     * @param int $endTime
     * @param Collection $swaps
     */
    public function __construct(
        protected string $exchange,
        protected string $ticker,
        protected string $baseSymbol,
        protected string $quoteSymbol,
        protected int    $startTime,
        protected int    $endTime,
        Collection       $swaps
    )
    {
        $this->afterPrice = $swaps->last()->getPrice();
        $this->afterBaseAmount = $swaps->sum('base_amount');
        $this->afterQuoteAmount = $swaps->sum('quote_amount');
        $this->afterQuoteAmountEquivalent = num_add($this->afterPrice * $this->afterBaseAmount, $this->afterQuoteAmount);
        // first swap is initial
        tap($swaps->shift(), function (SwapTest $swap) {
            $this->beforePrice = $swap->getPrice();
            $this->beforeBaseAmount = $swap->getBaseAmount();
            $this->beforeQuoteAmount = $swap->getQuoteAmount();
            $this->beforeQuoteAmountEquivalent = num_add($this->beforePrice * $this->beforeBaseAmount, $this->beforeQuoteAmount);
        });

        $this->profit = bcsub($this->afterQuoteAmountEquivalent, $this->beforeQuoteAmountEquivalent);
        $this->profitPercent = num_mul($this->profit / $this->beforeQuoteAmountEquivalent, 100, 2);
        $this->shownProfitPercent = sprintf('%s%%', $this->profitPercent);
        $this->shownStartTime = date(DATE_DEFAULT, $this->startTime);
        $this->shownEndTime = date(DATE_DEFAULT, $this->endTime);
        $this->trades = $swaps->map(function (SwapTest $swap) {
            return $swap->quoteSwapped()
                ? sprintf('[%s] Buy %s %s from %s %s @ %s',
                    date(DATE_DEFAULT, $swap->getTime()),
                    $swap->getBaseAmount(),
                    $this->baseSymbol,
                    -$swap->getQuoteAmount(),
                    $this->quoteSymbol,
                    $swap->getPrice())
                : sprintf('[%s] Sell %s %s to %s %s @ %s',
                    date(DATE_DEFAULT, $swap->getTime()),
                    -$swap->getBaseAmount(),
                    $this->baseSymbol,
                    $swap->getQuoteAmount(),
                    $this->quoteSymbol,
                    $swap->getPrice());
        })->all();
    }
}
