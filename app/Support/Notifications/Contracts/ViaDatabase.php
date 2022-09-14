<?php

namespace App\Support\Notifications\Contracts;

interface ViaDatabase
{
    public function toDatabase(Notifiable $notifiable): array;
}
