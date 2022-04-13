<?php

namespace App\Mail;

use App\Support\Mail\QueueableMailable;

class TrialQueueableMailable extends QueueableMailable
{
    public function build()
    {
        $this->text('trial_plain', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}