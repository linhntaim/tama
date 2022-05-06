<?php

namespace App\Mail\Trial;

use App\Support\Mail\QueueableMailable;

class LocaleViewQueueableMailable extends QueueableMailable
{
    protected bool $viewOnLocale = true;

    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
