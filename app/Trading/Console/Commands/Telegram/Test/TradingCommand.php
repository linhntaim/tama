<?php

namespace App\Trading\Console\Commands\Telegram\Test;

use App\Trading\Bots\Tests\ResultTest;
use App\Trading\Bots\Tests\TradingTest;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class TradingCommand extends Command
{
    public $signature = '{buy_trading_id} {sell_trading_id?} {--base-amount=0.0} {--quote-amount=500.0} {--buy-risk=} {--sell-risk=} {--start-time=1Y} {--end-time=}';

    protected $description = 'Test existing tradings.';

    protected function buyTradingId(): int
    {
        return $this->argument('buy_trading_id');
    }

    protected function sellTradingId(): ?int
    {
        return is_null($id = $this->argument('sell_trading_id')) ? null : (int)$id;
    }

    protected function handling(): int
    {
        $tradingProvider = new TradingProvider();
        if (is_null($buyTrading = $tradingProvider
            ->notStrict()
            ->firstByKey($this->buyTradingId()))) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Buy trading not found.'
            );
        }
        else {
            $sellTrading = is_null($sellTradingId = $this->sellTradingId()) ? $buyTrading : $tradingProvider
                ->notStrict()
                ->firstByKey($sellTradingId);
            if (is_null($sellTrading)) {
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    'Sell Trading not found.'
                );
            }
            else {
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    $this->printTestTrading($buyTrading, $sellTrading)
                );
            }
        }
        return $this->exitSuccess();
    }

    protected function printTestTrading(Trading $buyTrading, Trading $sellTrading): string
    {
        return transform(
            $this->testTrading($buyTrading, $sellTrading),
            function (ResultTest $result) use ($buyTrading, $sellTrading) {
                return implode(PHP_EOL, [
                    sprintf('Test for buy trading {#%d} and sell trading {#%d}', $buyTrading->id, $sellTrading->id),
                    $this->printResultTest($result, $buyTrading, $sellTrading),
                ]);
            }
        );
    }

    protected function testTrading(Trading $buyTrading, Trading $sellTrading): ResultTest
    {
        return (new TradingTest(
            $buyTrading,
            $sellTrading,
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
