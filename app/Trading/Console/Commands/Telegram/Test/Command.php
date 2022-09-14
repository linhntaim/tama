<?php

namespace App\Trading\Console\Commands\Telegram\Test;

use App\Trading\Console\Commands\Telegram\Command as BaseCommand;
use App\Trading\Bots\Tests\ResultTest;
use App\Trading\Models\Trading;

abstract class Command extends BaseCommand
{
    public $signature = '{--base-amount=0.0} {--quote-amount=500.0} {--buy-risk=} {--sell-risk=} {--start-time=1Y} {--end-time=}';

    protected bool $queuedOnRequest = true;

    protected function baseAmount(): string
    {
        return $this->option('base-amount');
    }

    protected function quoteAmount(): string
    {
        return $this->option('quote-amount');
    }

    protected function buyRisk(): ?float
    {
        return is_null($risk = $this->option('buy-risk')) ? null : (float)$risk;
    }

    protected function sellRisk(): ?float
    {
        return is_null($risk = $this->option('sell-risk')) ? null : (float)$risk;
    }

    protected function startTime(): ?string
    {
        return $this->option('start-time');
    }

    protected function endTime(): ?string
    {
        return $this->option('end-time');
    }

    protected function printResultTest(ResultTest $result, Trading $buyTrading, Trading $sellTrading): string
    {
        return implode(PHP_EOL, [
            sprintf('- Buy: {#%d:%s} risk=%s', $buyTrading->id, $buyTrading->slug, $result->buyRisk),
            sprintf('- Sell: {#%d:%s} risk=%s', $sellTrading->id, $buyTrading->slug, $result->sellRisk),
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