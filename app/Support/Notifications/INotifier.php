<?php

namespace App\Support\Notifications;

interface INotifier
{
    public function getNotifierKey();

    public function getNotifierDisplayName();
}
