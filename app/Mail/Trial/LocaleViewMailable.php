<?php

namespace App\Mail\Trial;

use App\Support\Mail\Mailable;

class LocaleViewMailable extends Mailable
{
    protected bool $viewOnLocale = true;

    public function build(): void
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
