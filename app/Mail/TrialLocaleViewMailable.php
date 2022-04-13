<?php

namespace App\Mail;

use App\Support\Mail\Mailable;

class TrialLocaleViewMailable extends Mailable
{
    protected bool $viewOnLocale = true;

    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}