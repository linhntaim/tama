<?php

namespace App\Notifications\Trial;

use App\Support\Client\DateTimer;
use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\ViaDatabase;
use App\Support\Notifications\Notification;

class DatabaseNotification extends Notification implements ViaDatabase
{
    protected function dataDatabase(NotifiableContract $notifiable): array
    {
        return [
            'date' => DateTimer::databaseNow(),
        ];
    }
}
