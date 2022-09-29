<?php

namespace App\Trading\Console\Commands\Telegram\Test;

use App\Trading\Bots\Tests\Data\ResultTest;
use App\Trading\Bots\Tests\StrategyTest;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;

class StrategyCommand extends Command
{
    public $signature = '{id} {--base-amount=0.0} {--quote-amount=500.0} {--buy-risk=} {--sell-risk=} {--start-time=1Y} {--end-time=}';

    protected $description = 'Test an existing trading strategy.';

    protected function id(): int
    {
        return $this->argument('id');
    }

    protected function handling(): int
    {
        if (($user = $this->validateFindingUser()) !== false) {
            if (is_null($strategy = (new TradingStrategyProvider())
                    ->notStrict()
                    ->firstByKey($this->id())) || $strategy->user_id !== $user->id) {
                $this->sendConsoleNotification('Strategy not found.');
            }
            else {
                $this->sendConsoleNotification($this->printTestStrategy($strategy));
            }
        }
        return $this->exitSuccess();
    }

    protected function printTestStrategy(TradingStrategy $strategy): string
    {
        return transform(
            $this->testStrategy($strategy),
            function (ResultTest $result) use ($strategy) {
                return implode(PHP_EOL, [
                    sprintf('TEST STRATEGY {#%d}', $strategy->id),
                    str_repeat('‾', 25),
                    $this->printResultTest($result, $strategy->buyTradings, $strategy->sellTradings),
                ]);
            }
        );
    }

    protected function testStrategy(TradingStrategy $strategy): ResultTest
    {
        return (new StrategyTest(
            $strategy,
            $this->baseAmount(),
            $this->quoteAmount(),
            $this->buyRisk(),
            $this->sellRisk()
        ))
            ->test(
                $this->startTime(), $this->endTime()
            );
    }
}
