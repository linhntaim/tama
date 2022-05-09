<?php

namespace App\Notifications\Trial;

use App\Mail\TrialMailable;
use App\Support\Mail\Mailable;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaMail;

class MailNotification extends Notification implements ViaMail
{
    public function dataMailable(INotifiable $notifiable): ?Mailable
    {
        return (new TrialMailable())
            ->with('notifier', $this->notifier);
    }
}
