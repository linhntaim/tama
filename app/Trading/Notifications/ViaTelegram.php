<?php

namespace App\Trading\Notifications;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use NotificationChannels\Telegram\TelegramMessage;

interface ViaTelegram
{
    public function toTelegram(NotifiableContract $notifiable): TelegramMessage;
}
