<?php

namespace App\Trading\Bots\Tests;

use Illuminate\Support\Collection;

class ResultTest
{
    protected string $beforePrice;

    protected string $beforeBaseAmount;

    protected string $beforeQuoteAmount;

    protected string $beforeQuoteAmountEquivalent;

    protected string $afterPrice;

    protected string $afterBaseAmount;

    protected string $afterQuoteAmount;

    protected string $afterQuoteAmountEquivalent;

    protected string $profit;

    protected string $profitPercent;

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
        tap($swaps->shift(), function (SwapTest $swap) {
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
        $this->trades = $swaps->map(function (SwapTest $swap) {
            return $swap->quoteSwapped()
                ? sprintf('[%s] Buy %s %s from %s %s @ %s',
                    date(DATE_DEFAULT, $swap->getTime()),
                    $swap->getBaseAmount(),
                    $this->baseSymbol,
                    num_neg($swap->getQuoteAmount()),
                    $this->quoteSymbol,
                    $swap->getPrice())
                : sprintf('[%s] Sell %s %s to %s %s @ %s',
                    date(DATE_DEFAULT, $swap->getTime()),
                    num_neg($swap->getBaseAmount()),
                    $this->baseSymbol,
                    $swap->getQuoteAmount(),
                    $this->quoteSymbol,
                    $swap->getPrice());
        })->all();
    }
}
