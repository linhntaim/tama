<?php

namespace App\Support\Notifications;

interface INotifiable
{
    /**
     * @param string $driver
     * @param Notification|null $notification
     * @return mixed
     */
    public function routeNotificationFor($driver, $notification = null);
}