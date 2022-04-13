<?php

namespace App\Mail;

use App\Support\Mail\QueueableMailable;

class TrialViewQueueableMailable extends QueueableMailable
{
    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}