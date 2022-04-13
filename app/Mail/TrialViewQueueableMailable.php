<?php

namespace App\Mail;

use App\Support\Mail\Mailable;

class TrialViewQueueableMailable extends Mailable
{
    protected function sendBefore()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}