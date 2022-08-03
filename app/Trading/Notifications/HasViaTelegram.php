<?php

namespace App\Trading\Notifications;

use App\Support\Notifications\INotifiable;
use BadMethodCallException;
use NotificationChannels\Telegram\TelegramMessage;

trait HasViaTelegram
{
    public function via(INotifiable $notifiable): array|string
    {
        $via = parent::via($notifiable);
        if ($this instanceof ViaTelegram) {
            $via[] = 'telegram';
        }
        return $via;
    }

    public function toTelegram(INotifiable $notifiable): TelegramMessage
    {
        return $this->dataTelegram($notifiable);
    }

    public function dataTelegram(INotifiable $notifiable): TelegramMessage
    {
        throw new BadMethodCallException('Method does not exist.');
    }
}
