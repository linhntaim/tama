<?php

namespace App\Notifications;

use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaBroadcast;

class TrialBroadcastNotification extends Notification implements ViaBroadcast
{
    public function toArray(INotifiable $notifiable): array
    {
        return [
            'notifier' => $this->notifier ? [
                'display_name' => $this->notifier->getNotifierDisplayName(),
            ] : null,
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ];
    }
}