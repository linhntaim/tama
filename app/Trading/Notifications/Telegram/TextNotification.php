<?php

namespace App\Trading\Notifications\Telegram;

use App\Support\Notifications\INotifiable;
use App\Support\Notifications\INotifier;
use App\Support\Notifications\Notification;
use App\Trading\Notifications\HasViaTelegram;
use App\Trading\Notifications\ViaTelegram;
use NotificationChannels\Telegram\TelegramMessage;

class TextNotification extends Notification implements ViaTelegram
{
    use HasViaTelegram;

    public function __construct(
        protected string $text,
        ?INotifier       $notifier = null
    )
    {
        parent::__construct($notifier);
    }

    protected function getText(INotifiable $notifiable): string
    {
        return $this->text;
    }

    public function dataTelegram(INotifiable $notifiable): TelegramMessage
    {
        return TelegramMessage::create($this->getText($notifiable));
    }
}
