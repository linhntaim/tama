<?php

namespace App\Support\Notifications;

interface ViaBroadcast
{
    public function toArray(INotifiable $notifiable): array;
}