<?php

namespace App\Notifications\Trial;

use App\Mail\Trial\Mailable as TrialMailable;
use App\Support\Mail\Mailable;
use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\ViaMail;
use App\Support\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MailNotification extends Notification implements ViaMail
{
    public function dataMailable(NotifiableContract $notifiable): Mailable|MailMessage|null
    {
        return (new TrialMailable())
            ->with('notifier', $this->notifier);
    }
}
