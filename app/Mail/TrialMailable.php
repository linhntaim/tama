<?php

namespace App\Mail;

use App\Support\Mail\Mailable;

class TrialMailable extends Mailable
{
    protected function sendBefore()
    {
        $this->text(date_timer()->compound('longDate', ' ', 'longTime'));
    }
}