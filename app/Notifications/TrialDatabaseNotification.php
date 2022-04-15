<?php

namespace App\Notifications;

use App\Support\Client\DateTimer;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaDatabase;

class TrialDatabaseNotification extends Notification implements ViaDatabase
{
    protected function dataDatabase(INotifiable $notifiable): array
    {
        return [
            'date' => DateTimer::databaseNow(),
        ];
    }
}