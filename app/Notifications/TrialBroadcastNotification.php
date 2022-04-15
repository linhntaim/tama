<?php

namespace App\Notifications;

use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaBroadcast;

class TrialBroadcastNotification extends Notification implements ViaBroadcast
{
    protected function dataBroadcast(INotifiable $notifiable): array
    {
        return [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ];
    }
}