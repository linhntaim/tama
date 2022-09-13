<?php

namespace App\Trading\Console\Commands\Telegram\Test;

use App\Trading\Bots\Tests\ResultTest;
use App\Trading\Bots\Tests\TradingStrategyTest;
use App\Trading\Console\Commands\Telegram\FindUser;
use App\Trading\Models\TradingStrategy;
use App\Trading\Models\TradingStrategyProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class StrategyCommand extends Command
{
    use FindUser;

    public $signature = '{id} {--base-amount=0.0} {--quote-amount=500.0} {--buy-risk=} {--sell-risk=} {--start-time=1Y} {--end-time=}';

    protected $description = 'Test an existing trading strategy.';

    protected function id(): int
    {
        return $this->argument('id');
    }

    protected function handling(): int
    {
        if (is_null($user = $this->findUser())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Action is not supported.'
            );
        }
        elseif (is_null($strategy = (new TradingStrategyProvider())
                ->notStrict()
                ->firstByKey($this->id())) || $strategy->user_id !== $user->id) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Strategy not found.'
            );
        }
        else {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                $this->printTestStrategy($strategy)
            );
        }
        return $this->exitSuccess();
    }

    protected function printTestStrategy(TradingStrategy $strategy): string
    {
        return transform(
            $this->testStrategy($strategy),
            function (ResultTest $result) use ($strategy) {
                return implode(PHP_EOL, [
                    sprintf('Test for strategy {#%d}', $strategy->id),
                    $this->printResultTest($result, $strategy->buyTrading, $strategy->sellTrading),
                ]);
            }
        );
    }

    protected function testStrategy(TradingStrategy $strategy): ResultTest
    {
        return (new TradingStrategyTest(
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
