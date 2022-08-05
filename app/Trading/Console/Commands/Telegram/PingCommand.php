<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Notifications\Telegram\PingNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class PingCommand extends Command
{
    protected $description = 'Check if the bot is responsible (alias: /hello).';

    protected function handling(): int
    {
        PingNotification::send(new TelegramUpdateNotifiable($this->telegramUpdate));
        return $this->exitSuccess();
    }
}
