<?php

namespace App\Trading\Notifications;

use App\Support\Notifications\INotifiable;
use NotificationChannels\Telegram\TelegramMessage;

interface ViaTelegram
{
    public function toTelegram(INotifiable $notifiable): TelegramMessage;
}
