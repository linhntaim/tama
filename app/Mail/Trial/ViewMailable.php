<?php

namespace App\Mail\Trial;

use App\Support\Mail\Mailable;

class ViewMailable extends Mailable
{
    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
