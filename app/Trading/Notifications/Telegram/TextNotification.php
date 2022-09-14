<?php

namespace App\Trading\Notifications\Telegram;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\Notifier as NotifierContract;
use App\Support\Notifications\Notification;
use App\Trading\Notifications\HasViaTelegram;
use App\Trading\Notifications\ViaTelegram;
use NotificationChannels\Telegram\TelegramMessage;

class TextNotification extends Notification implements ViaTelegram
{
    use HasViaTelegram;

    public function __construct(
        protected string $text,
        ?NotifierContract       $notifier = null
    )
    {
        parent::__construct($notifier);
    }

    protected function getText(NotifiableContract $notifiable): string
    {
        return $this->text;
    }

    public function dataTelegram(NotifiableContract $notifiable): TelegramMessage
    {
        return TelegramMessage::create($this->getText($notifiable));
    }
}
