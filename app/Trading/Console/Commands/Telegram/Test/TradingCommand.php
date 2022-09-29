<?php

namespace App\Trading\Console\Commands\Telegram\Test;

use App\Trading\Bots\Tests\Data\ResultTest;
use App\Trading\Bots\Tests\TradingTest;
use App\Trading\Console\Commands\Telegram\InteractsWithTradings;
use Illuminate\Database\Eloquent\Collection;

class TradingCommand extends Command
{
    use InteractsWithTradings;

    public $signature = '{buy_trading_ids} {sell_trading_ids?} {--base-amount=0.0} {--quote-amount=500.0} {--buy-risk=} {--sell-risk=} {--start-time=1Y} {--end-time=}';

    protected $description = 'Test existing tradings.';

    protected function handling(): int
    {
        if (($tradings = $this->validateTradings()) !== false) {
            [$buyTradings, $sellTradings] = $tradings;
            $this->sendConsoleNotification($this->printTestTrading($buyTradings, $sellTradings));
        }
        return $this->exitSuccess();
    }

    protected function printTestTrading(Collection $buyTradings, Collection $sellTradings): string
    {
        return transform(
            $this->testTrading($buyTradings, $sellTradings),
            function (ResultTest $result) use ($buyTradings, $sellTradings) {
                return implode(PHP_EOL, [
                    'TEST TRADINGS',
                    str_repeat('‾', 25),
                    $this->printResultTest($result, $buyTradings, $sellTradings),
                ]);
            }
        );
    }

    protected function testTrading(Collection $buyTradings, Collection $sellTradings): ResultTest
    {
        return (new TradingTest(
            $buyTradings,
            $sellTradings,
            $this->baseAmount(),
            $this->quoteAmount(),
            $this->buyRisk() ?: 0.0,
            $this->sellRisk() ?: 0.0
        ))
            ->test(
                $this->startTime(), $this->endTime()
            );
    }
}
