<?php

namespace App\Support\Notifications\Channels;

use App\Support\Notifications\Notification;

interface IChannel
{
    public function send(mixed $notifiable, Notification $notification): mixed;
}