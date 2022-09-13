<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Notifications\Telegram\PingNotification;

class PingCommand extends Command
{
    protected $description = 'Check if the bot is responsible (alias: /hello).';

    protected function handling(): int
    {
        PingNotification::send($this->getTelegramNotifiable());
        return $this->exitSuccess();
    }
}
