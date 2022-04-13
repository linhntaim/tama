<?php

namespace App\Mail;

use App\Support\Mail\Mailable;

class TrialViewMailable extends Mailable
{
    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}