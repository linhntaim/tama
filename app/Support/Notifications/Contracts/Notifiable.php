<?php

namespace App\Support\Notifications\Contracts;

use App\Support\Notifications\Notification;

interface Notifiable
{
    /**
     * @param string $driver
     * @param Notification|null $notification
     * @return mixed
     */
    public function routeNotificationFor($driver, $notification = null);
}
