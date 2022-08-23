<?php

namespace App\Listeners;

use App\Support\Auth\Contracts\MustWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendEmailWelcomeNotification
{
    public function handle(Registered $event)
    {
        if (!$event->user instanceof MustVerifyEmail && $event->user instanceof MustWelcomeEmail) {
            $event->user->sendEmailWelcomeNotification();
        }
    }
}
