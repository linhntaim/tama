<?php

namespace App\Support\Contracts\Auth;

interface MustWelcomeEmail
{
    public function sendEmailWelcomeNotification(): void;

    public function getEmailForWelcome(): string;
}
