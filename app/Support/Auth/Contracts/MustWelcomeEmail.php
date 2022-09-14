<?php

namespace App\Support\Auth\Contracts;

interface MustWelcomeEmail
{
    public function sendEmailWelcomeNotification(): void;

    public function getEmailForWelcome(): string;
}
