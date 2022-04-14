<?php

namespace App\Notifications;

use App\Mail\TrialMailable;
use App\Support\Mail\Mailable;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\Notification;
use App\Support\Notifications\ViaMail;

class TrialMailNotification extends Notification implements ViaMail
{
    public function toMail(INotifiable $notifiable): Mailable
    {
        return $this->dataMailable($notifiable)->to($notifiable);
    }

    public function dataMailable(INotifiable $notifiable): Mailable
    {
        return (new TrialMailable())
            ->with('notifier', $this->notifier);
    }
}