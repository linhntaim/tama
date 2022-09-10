<?php

namespace App\Trading\Console\Commands\Telegram\Trading;

use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Console\Commands\Telegram\Command;
use App\Trading\Console\Commands\Telegram\CreateTrading;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use App\Trading\Trader;

class CreateCommand extends Command
{
    use CreateTrading;

    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Create a trading.';

    protected function handling(): int
    {
        if (!Exchanger::available($this->exchange())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Trading for the exchange "%s" was not supported/enabled.', $this->exchange())
            );
        }
        elseif (in_array($this->interval(), [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
        ], true)) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Trading for the interval "%s" was not supported/enabled.', $this->interval())
            );
        }
        else {
            $trading = $this->createTrading();
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Trading {#%s:%s} was created successfully.', $trading->id, $trading->slug)
            );
        }
        return $this->exitSuccess();
    }
}
