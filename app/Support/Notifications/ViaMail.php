<?php

namespace App\Support\Notifications;

use App\Support\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;

interface ViaMail
{
    public function toMail(INotifiable $notifiable): Mailable|MailMessage|null;
}
