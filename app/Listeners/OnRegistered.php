<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Support\Listeners\Listener;
use App\Support\Mail\SendMail;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class OnRegistered extends Listener
{
    use SendMail;

    /**
     * @param Registered $event
     */
    protected function handling($event)
    {
        if ($event->user instanceof MustVerifyEmail) {
            $this->sendEmailVerificationNotification($event);
        }
        else {
            $this->sendEmailRegistrationNotification($event);
        }
    }

    /**
     * @param Registered $event
     */
    protected function sendEmailVerificationNotification($event)
    {
        if (!$event->user->hasVerifiedEmail()) {
            $event->user->sendEmailVerificationNotification();
        }
    }

    /**
     * @param Registered $event
     */
    protected function sendEmailRegistrationNotification($event)
    {
        $event->user->sendEmailRegistrationNotification($event->password);
    }
}
