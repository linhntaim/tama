<?php

namespace App\Trading\Notifications\Telegram;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Trading\Services\Telegram\MarkdownConsole;

class ConsoleNotification extends TextNotification
{
    protected function getText(NotifiableContract $notifiable): string
    {
        return new MarkdownConsole($this->text);
    }
}
