<?php

namespace App\Notifications\Trial;

use App\Mail\Trial\Mailable as TrialMailable;
use App\Support\Mail\Mailable;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaMail;
use Illuminate\Notifications\Messages\MailMessage;

class MailNotification extends Notification implements ViaMail
{
    public function dataMailable(INotifiable $notifiable): Mailable|MailMessage|null
    {
        return (new TrialMailable())
            ->with('notifier', $this->notifier);
    }
}
