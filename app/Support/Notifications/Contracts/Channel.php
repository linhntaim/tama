<?php

namespace App\Support\Notifications\Contracts;

use App\Support\Notifications\Notification;

interface Channel
{
    public function send(Notifiable $notifiable, Notification $notification): mixed;
}
