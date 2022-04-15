<?php

namespace App\Support\Notifications;

use Illuminate\Contracts\Bus\Dispatcher as Bus;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\ChannelManager as BaseChannelManager;

class ChannelManager extends BaseChannelManager
{
    public function send($notifiables, $notification)
    {
        (new NotificationSender(
            $this, $this->container->make(Bus::class), $this->container->make(Dispatcher::class), $this->locale)
        )->send($notifiables, $notification);
    }

    public function sendNow($notifiables, $notification, array $channels = null)
    {
        (new NotificationSender(
            $this, $this->container->make(Bus::class), $this->container->make(Dispatcher::class), $this->locale)
        )->sendNow($notifiables, $notification, $channels);
    }
}