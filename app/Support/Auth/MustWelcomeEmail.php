<?php

namespace App\Support\Auth;

use App\Support\Auth\Notifications\WelcomeEmail;

trait MustWelcomeEmail
{
    public function sendEmailWelcomeNotification(): void
    {
        $this->notify(new WelcomeEmail());
    }

    public function getEmailForWelcome(): string
    {
        return $this->email;
    }
}
