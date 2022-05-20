<?php

namespace App\Support\Notifications;

use Illuminate\Notifications\RoutesNotifications;

trait Notifiable
{
    use HasDatabaseNotifications, RoutesNotifications;
}
