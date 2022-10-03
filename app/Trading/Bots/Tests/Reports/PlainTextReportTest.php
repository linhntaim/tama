<?php

namespace App\Trading\Bots\Tests\Reports;

use App\Trading\Bots\Tests\Data\ResultTest;

class PlainTextReportTest implements IReportTest
{
    public function report(ResultTest $result): string
    {
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