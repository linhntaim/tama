<?php

namespace App\Support\Notifications;

use App\Support\Mail\Mailable;

interface ViaMail
{
    public function toMail(INotifiable $notifiable): Mailable;
}