<?php

namespace App\Mail;

use App\Support\Mail\QueueableMailable;

class TrialLocaleViewQueueableMailable extends QueueableMailable
{
    protected bool $viewOnLocale = true;

    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}