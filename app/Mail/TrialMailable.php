<?php

namespace App\Mail;

use App\Support\Mail\Mailable;

class TrialMailable extends Mailable
{
    public function build()
    {
        $this->text('trial_plain', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}