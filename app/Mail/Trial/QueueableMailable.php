<?php

namespace App\Mail\Trial;

use App\Support\Mail\QueueableMailable as BaseQueueableMailable;

class QueueableMailable extends BaseQueueableMailable
{
    public function build(): void
    {
        $this->text('trial_plain', [
            'date' => date_timer()->compound('longDate', ' ', 'longTime'),
        ]);
    }
}
