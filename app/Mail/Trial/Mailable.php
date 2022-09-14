<?php

namespace App\Mail\Trial;

use App\Support\Mail\Mailable as BaseMailable;

class Mailable extends BaseMailable
{
    public function build(): void
    {
        $this->text('trial_plain', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
