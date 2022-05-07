<?php

namespace App\Notifications\Trial;

use App\Support\Client\DateTimer;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaDatabase;

class DatabaseNotification extends Notification implements ViaDatabase
{
    protected function dataDatabase(INotifiable $notifiable): array
    {
        return [
            'date' => DateTimer::databaseNow(),
        ];
    }
}
