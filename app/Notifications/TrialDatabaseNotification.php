<?php

namespace App\Notifications;

use App\Support\Client\DateTimer;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaDatabase;

class TrialDatabaseNotification extends Notification implements ViaDatabase
{
    public function toDatabase(INotifiable $notifiable): array
    {
        return [
            'notifier' => $this->notifier ? [
                'type' => $this->notifier::class,
                'id' => $this->notifier->getNotifierKey(),
            ] : null,
            'date' => DateTimer::databaseNow(),
        ];
    }
}