<?php

namespace App\Support\Notifications;

interface ViaDatabase
{
    public function toDatabase(INotifiable $notifiable): array;
}