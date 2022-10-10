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
        $countMonths = $months->count();
        $monthlyPrices = Exchanger::connector($result->exchange)->recentPricesAt($result->ticker, new Interval('1M'), $months->last(), $countMonths);
        $highestOverallProfit = [
            'month' => null,
            'profit' => '0',
            'profit_percent' => '0',
        ];
        $lowestOverallProfit = [
            'month' => null,
            'profit' => '0',
            'profit_percent' => '0',
        ];
        $highestMonthlyProfit = [
            'month' => null,
            'profit' => '0',
            'profit_percent' => '0',
        ];
        $lowestMonthlyProfit = [
            'month' => null,
            'profit' => '0',
            'profit_percent' => '0',
        ];
        $sumMonthlyProfit = [
            'profit' => '0',
            'profit_percent' => '0',
        ];
        $updatePivotProfit = static function (array &$highestProfit, array &$lowestProfit, int $month, string $profit, string $profitPercent) {
            if (is_null($highestProfit['month']) || num_gte($profit, $highestProfit['profit'])) {
                $highestProfit['month'] = $month;
                $highestProfit['profit'] = $profit;
                $highestProfit['profit_percent'] = $profitPercent;
            }
            if (is_null($lowestProfit['month']) || num_lte($profit, $lowestProfit['profit'])) {
                $lowestProfit['month'] = $month;
                $lowestProfit['profit'] = $profit;
                $lowestProfit['profit_percent'] = $profitPercent;
            }
        };
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
            $updatePivotProfit($highestMonthlyProfit, $lowestMonthlyProfit, $month, $monthlyProfit, $monthlyProfitPercent);
            $sumMonthlyProfit['profit'] = num_add($sumMonthlyProfit['profit'], $monthlyProfit);
            $sumMonthlyProfit['profit_percent'] = num_add($sumMonthlyProfit['profit_percent'], $monthlyProfitPercent);

            $overallProfit = num_sub($endQuoteAmountEquivalent, $result->beforeQuoteAmountEquivalent);
            $overallProfitPercent = num_mul(num_div($overallProfit, $result->beforeQuoteAmountEquivalent), 100, 2);
            $updatePivotProfit($highestOverallProfit, $lowestOverallProfit, $month, $overallProfit, $overallProfitPercent);

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
                    '- Profit: %s %s ~ %s%% (monthly) / %s %s ~ %s%% (overall)',
                    num_trim($monthlyProfit),
                    $result->quoteSymbol,
                    $monthlyProfitPercent,
                    num_trim($overallProfit),
                    $result->quoteSymbol,
                    $overallProfitPercent
                ),
            ]);
        }
        array_push(
            $this->summary,
            ...(is_null($highestOverallProfit['month']) ? ['', '', ''] : [
            $highestOverallProfit['profit'],
            $highestOverallProfit['profit_percent'],
            Carbon::createFromTimestamp($highestOverallProfit['month'])->format('Y-m'),
        ]),
            ...(is_null($highestOverallProfit['month']) ? ['', '', ''] : [
            $lowestOverallProfit['profit'],
            $lowestOverallProfit['profit_percent'],
            Carbon::createFromTimestamp($lowestOverallProfit['month'])->format('Y-m'),
        ]),
            ...(is_null($highestMonthlyProfit['month']) ? ['', '', ''] : [
            $highestMonthlyProfit['profit'],
            $highestMonthlyProfit['profit_percent'],
            Carbon::createFromTimestamp($highestMonthlyProfit['month'])->format('Y-m'),
        ]),
            ...(is_null($lowestMonthlyProfit['month']) ? ['', '', ''] : [
            $lowestMonthlyProfit['profit'],
            $lowestMonthlyProfit['profit_percent'],
            Carbon::createFromTimestamp($lowestMonthlyProfit['month'])->format('Y-m'),
        ]),
            ...($countMonths === 0 ? ['', ''] : [
            num_div($sumMonthlyProfit['profit'], $countMonths),
            num_div($sumMonthlyProfit['profit_percent'], $countMonths, 2),
        ])
        );
        array_unshift(
            $reports,
            '- Summary:',
            is_null($highestOverallProfit['month']) ? '+ Highest overall profit: **unknown**' : sprintf(
                '+ Highest overall profit: %s %s ~ %s%% in %s',
                num_trim($highestOverallProfit['profit']),
                $result->quoteSymbol,
                $highestOverallProfit['profit_percent'],
                Carbon::createFromTimestamp($highestOverallProfit['month'])->format('Y-m')
            ),
            is_null($lowestOverallProfit['month']) ? '+ Lowest overall profit: **unknown**' : sprintf(
                '+ Lowest overall profit: %s %s ~ %s%% in %s',
                num_trim($lowestOverallProfit['profit']),
                $result->quoteSymbol,
                $lowestOverallProfit['profit_percent'],
                Carbon::createFromTimestamp($lowestOverallProfit['month'])->format('Y-m')
            ),
            is_null($highestMonthlyProfit['month']) ? '+ Highest monthly profit: **unknown**' : sprintf(
                '+ Highest monthly profit: %s %s ~ %s%% in %s',
                num_trim($highestMonthlyProfit['profit']),
                $result->quoteSymbol,
                $highestMonthlyProfit['profit_percent'],
                Carbon::createFromTimestamp($highestMonthlyProfit['month'])->format('Y-m')
            ),
            is_null($lowestMonthlyProfit['month']) ? '+ Lowest monthly profit: **unknown**' : sprintf(
                '+ Lowest monthly profit: %s %s ~ %s%% in %s',
                num_trim($lowestMonthlyProfit['profit']),
                $result->quoteSymbol,
                $lowestMonthlyProfit['profit_percent'],
                Carbon::createFromTimestamp($lowestMonthlyProfit['month'])->format('Y-m')
            ),
            $countMonths === 0 ? '+ Avg. monthly profit: **unknown**' : sprintf(
                '+ Avg. monthly profit: %s %s ~ %s%%',
                num_trim(num_div($sumMonthlyProfit['profit'], $countMonths)),
                $result->quoteSymbol,
                num_div($sumMonthlyProfit['profit_percent'], $countMonths, 2)
            ),

            str_repeat('-', 25),
        );
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