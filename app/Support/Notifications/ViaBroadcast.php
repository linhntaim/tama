<?php

namespace App\Support\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;

interface ViaBroadcast
{
    public function toBroadcast(INotifiable $notifiable): BroadcastMessage;
}
