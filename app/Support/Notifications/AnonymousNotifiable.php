<?php

namespace App\Support\Notifications;

use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use Illuminate\Notifications\AnonymousNotifiable as BaseAnonymousNotifiable;

class AnonymousNotifiable extends BaseAnonymousNotifiable implements NotifiableContract
{
    public function routeNotificationFor($driver, $notification = null)
    {
        return parent::routeNotificationFor($driver);
    }
}
