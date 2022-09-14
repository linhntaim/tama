<?php

namespace App\Support\Auth\Listeners;

use App\Support\Auth\Contracts\MustWelcomeEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendEmailWelcomeNotification
{
    public function handle(Registered $event): void
    {
        if (!$event->user instanceof MustVerifyEmail && $event->user instanceof MustWelcomeEmail) {
            $event->user->sendEmailWelcomeNotification();
        }
    }
}
