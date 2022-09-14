<?php

namespace App\Support\Notifications\Contracts;

use Illuminate\Notifications\Messages\BroadcastMessage;

interface ViaBroadcast
{
    public function toBroadcast(Notifiable $notifiable): BroadcastMessage;
}
