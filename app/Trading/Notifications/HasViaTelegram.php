<?php

namespace App\Trading\Notifications;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use BadMethodCallException;
use NotificationChannels\Telegram\TelegramMessage;

trait HasViaTelegram
{
    public function via(NotifiableContract $notifiable): array|string
    {
        $via = (array)parent::via($notifiable);
        if ($this instanceof ViaTelegram) {
            $via[] = 'telegram';
        }
        return $via;
    }

    public function toTelegram(NotifiableContract $notifiable): TelegramMessage
    {
        return $this->dataTelegram($notifiable);
    }

    public function dataTelegram(NotifiableContract $notifiable): TelegramMessage
    {
        throw new BadMethodCallException('Method does not exist.');
    }
}
