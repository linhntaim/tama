<?php

namespace App\Trading\Notifications\Telegram;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\Notifier as NotifierContract;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class PingNotification extends TextNotification
{
    public function __construct(string $text = 'Hello', ?NotifierContract $notifier = null)
    {
        parent::__construct($text, $notifier);
    }

    protected function getText(NotifiableContract $notifiable): string
    {
        return $notifiable instanceof TelegramUpdateNotifiable && !$notifiable->isChannel()
            ? sprintf('%s @%s!', $this->text, $notifiable->fromUsername())
            : sprintf('%s!', $this->text);
    }
}
