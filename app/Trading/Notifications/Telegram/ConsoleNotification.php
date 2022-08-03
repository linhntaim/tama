<?php

namespace App\Trading\Notifications\Telegram;

use App\Support\Notifications\INotifiable;
use App\Trading\Telegram\MarkdownConsole;

class ConsoleNotification extends TextNotification
{
    protected function getText(INotifiable $notifiable): string
    {
        return new MarkdownConsole($this->text);
    }
}
