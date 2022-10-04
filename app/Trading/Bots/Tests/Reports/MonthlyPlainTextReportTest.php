<?php

namespace App\Trading\Bots\Tests\Reports;

use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\Interval;
use App\Trading\Bots\Tests\Data\ResultTest;
use App\Trading\Bots\Tests\Data\SwapTest;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MonthlyPlainTextReportTest extends PlainTextReportTest
{
    public function report(ResultTest $result): string
    {
        return implode(PHP_EOL, [
            parent::report($result),
            str_repeat('-', 25),
            $this->reportMonthly($result),
            str_repeat('-', 25),
        ]);
    }

    protected function reportMonthly(ResultTest $result): string
    {
        $reports = [];
        $monthlySwaps = $this->getMonthlySwaps($result);
        $monthlyAmounts = $this->getMonthlyAmounts($monthlySwaps);
        $months = $monthlySwaps->keys();
        $monthlyPrices = Exchanger::connector($result->exchange)->recentPricesAt($result->ticker, new Interval('1M'), $months->last(), $months->count());
        foreach ($months as $i => $month) {
            $swaps = $monthlySwaps[$month];

            $startPrice = $i === 0 ? $result->beforePrice : $monthlyPrices->priceAt($i - 1);
            [$startBaseAmount, $startQuoteAmount] = $i === 0
                ? [$result->beforeBaseAmount, $result->beforeQuoteAmount]
                : $monthlyAmounts[$i - 1];
            $startQuoteAmountEquivalent = num_add(num_mul($startPrice, $startBaseAmount), $startQuoteAmount);
            $endPrice = $monthlyPrices->priceAt($i);
            [$endBaseAmount, $endQuoteAmount] = $monthlyAmounts[$i];
            $endQuoteAmountEquivalent = num_add(num_mul($endPrice, $endBaseAmount), $endQuoteAmount);

            $monthlyProfit = num_sub($endQuoteAmountEquivalent, $startQuoteAmountEquivalent);
            $monthlyProfitPercent = num_mul(num_div($monthlyProfit, $startQuoteAmountEquivalent), 100, 2);
            $profit = num_sub($endQuoteAmountEquivalent, $result->beforeQuoteAmountEquivalent);
            $profitPercent = num_mul(num_div($profit, $result->beforeQuoteAmountEquivalent), 100, 2);

            $reports[] = implode(PHP_EOL, [
                sprintf(
                    '[%s]',
                    Carbon::createFromTimestamp($month)->format('Y-m')
                ),
                sprintf(
                    '- Start amount: %s %s + %s %s ~ %s %s',
                    num_trim($startBaseAmount),
                    $result->baseSymbol,
                    num_trim($startQuoteAmount),
                    $result->quoteSymbol,
                    num_trim($startQuoteAmountEquivalent),
                    $result->quoteSymbol
                ),
                sprintf(
                    '- End amount: %s %s + %s %s ~ %s %s',
                    num_trim($endBaseAmount),
                    $result->baseSymbol,
                    num_trim($endQuoteAmount),
                    $result->quoteSymbol,
                    num_trim($endQuoteAmountEquivalent),
                    $result->quoteSymbol
                ),
                sprintf(
                    '- Trades: %d ~ %d BUY / %d SELL',
                    $swaps
                        ->filter(fn(SwapTest $swap): bool => num_lt($swap->getQuoteAmount(), 0) || num_lt($swap->getBaseAmount(), 0))
                        ->count(),
                    $swaps
                        ->filter(fn(SwapTest $swap): bool => num_lt($swap->getQuoteAmount(), 0))
                        ->count(),
                    $swaps
                        ->filter(fn(SwapTest $swap): bool => num_lt($swap->getBaseAmount(), 0))
                        ->count()
                ),
                sprintf(
                    '- Profit: %s %s ~ %s%% (in month) / %s %s ~ %s%% (from start)',
                    num_trim($monthlyProfit),
                    $result->quoteSymbol,
                    $monthlyProfitPercent,
                    num_trim($profit),
                    $result->quoteSymbol,
                    $profitPercent
                ),
            ]);
        }
        return implode(PHP_EOL, $reports);
    }

    /**
     * @param ResultTest $result
     * @return Collection<int, Collection<int, SwapTest>>
     */
    protected function getMonthlySwaps(ResultTest $result): Collection
    {
        return with(
            $result->swaps
                ->groupBy(function (SwapTest $swap) {
                    return Carbon::createFromTimestamp($swap->getTime())
                        ->day(1)
                        ->hour(0)
                        ->minute(0)
                        ->second(0)
                        ->getTimestamp();
                }),
            static function (Collection $monthlySwaps) use ($result) {
                $loopMonth = Carbon::createFromTimestamp($result->startTime)
                    ->day(1)
                    ->hour(0)
                    ->minute(0)
                    ->second(0);
                $endMonth = Carbon::createFromTimestamp($result->endTime)
                    ->day(1)
                    ->hour(0)
                    ->minute(0)
                    ->second(0);
                $fullMonthlySwaps = [];
                while ($endMonth->gte($loopMonth)) {
                    $fullMonthlySwaps[$loopMonth->getTimestamp()] = $monthlySwaps->get($loopMonth->getTimestamp(), new Collection());
                    $loopMonth->addMonth();
                }
                return new Collection($fullMonthlySwaps);
            }
        );
    }

    /**
     * @param Collection<int, Collection<int, SwapTest>> $monthlySwaps
     * @return array<int, array<int, string>>
     */
    protected function getMonthlyAmounts(Collection $monthlySwaps): array
    {
        $amounts = [];
        $baseAmount = $quoteAmount = 0;
        foreach ($monthlySwaps as $swaps) {
            $swaps->each(function (SwapTest $swap) use (&$baseAmount, &$quoteAmount) {
                $baseAmount = num_add($baseAmount, $swap->getBaseAmount());
                $quoteAmount = num_add($quoteAmount, $swap->getQuoteAmount());
            });
            $amounts[] = [$baseAmount, $quoteAmount];
        }
        return $amounts;
    }
}