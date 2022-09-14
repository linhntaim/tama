<?php

namespace App\Notifications\Trial;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\ViaBroadcast;
use App\Support\Notifications\Notification;

class BroadcastNotification extends Notification implements ViaBroadcast
{
    protected function dataBroadcast(NotifiableContract $notifiable): array
    {
        return [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ];
    }
}
