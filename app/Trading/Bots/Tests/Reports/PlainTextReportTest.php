<?php

namespace App\Trading\Bots\Tests\Reports;

use App\Trading\Bots\Tests\Data\ResultTest;

class PlainTextReportTest extends ReportTest
{
    public function report(ResultTest $result): string
    {
        array_push(
            $this->summary,
            $result->exchange,
            $result->baseSymbol,
            $result->quoteSymbol,
            $result->shownStartTime,
            $result->shownEndTime,
            $result->beforeBaseAmount,
            $result->beforeQuoteAmount,
            $result->beforeQuoteAmountEquivalent,
            $result->afterBaseAmount,
            $result->afterQuoteAmount,
            $result->afterQuoteAmountEquivalent,
            $result->tradeSwaps()->count(),
            $result->buySwaps()->count(),
            $result->sellSwaps()->count(),
            $result->profit,
            $result->profitPercent,
        );

        return implode(PHP_EOL, [
            sprintf(
                '- Start at: %s',
                $result->shownStartTime
            ),
            sprintf(
                '- Start amount: %s %s + %s %s ~ %s %s',
                num_trim($result->beforeBaseAmount),
                $result->baseSymbol,
                num_trim($result->beforeQuoteAmount),
                $result->quoteSymbol,
                num_trim($result->beforeQuoteAmountEquivalent),
                $result->quoteSymbol
            ),
            sprintf(
                '- End at: %s',
                $result->shownEndTime
            ),
            sprintf(
                '- End amount: %s %s + %s %s ~ %s %s',
                num_trim($result->afterBaseAmount),
                $result->baseSymbol,
                num_trim($result->afterQuoteAmount),
                $result->quoteSymbol,
                num_trim($result->afterQuoteAmountEquivalent),
                $result->quoteSymbol
            ),
            sprintf(
                '- Trades: %d ~ %d BUY / %d SELL',
                $result->tradeSwaps()->count(),
                $result->buySwaps()->count(),
                $result->sellSwaps()->count()
            ),
            sprintf(
                '- Profit: %s %s ~ %s%%',
                num_trim($result->profit),
                $result->quoteSymbol,
                $result->profitPercent
            ),
        ]);
    }
}