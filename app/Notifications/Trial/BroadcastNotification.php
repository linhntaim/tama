<?php

namespace App\Notifications\Trial;

use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaBroadcast;

class BroadcastNotification extends Notification implements ViaBroadcast
{
    protected function dataBroadcast(INotifiable $notifiable): array
    {
        return [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ];
    }
}
