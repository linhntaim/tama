<?php

namespace App\Support\Notifications;

use Illuminate\Notifications\AnonymousNotifiable as BaseAnonymousNotifiable;

class AnonymousNotifiable extends BaseAnonymousNotifiable implements INotifiable
{
    public function routeNotificationFor($driver, $notification = null)
    {
        return parent::routeNotificationFor($driver);
    }
}
