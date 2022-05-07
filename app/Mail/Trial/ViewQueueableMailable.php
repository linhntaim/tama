<?php

namespace App\Mail\Trial;

use App\Support\Mail\QueueableMailable;

class ViewQueueableMailable extends QueueableMailable
{
    public function build()
    {
        $this->view('trial', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
