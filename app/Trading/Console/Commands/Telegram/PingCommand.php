<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Notifications\Telegram\PingNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class PingCommand extends Command
{
    protected function handling(): int
    {
        PingNotification::send(new TelegramUpdateNotifiable($this->telegramUpdate));
        return $this->exitSuccess();
    }
}
