<?php

namespace App\Support\Notifications\Contracts;

use App\Support\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

interface ViaMail
{
    public function toMail(Notifiable $notifiable): Mailable|MailMessage|null;
}
