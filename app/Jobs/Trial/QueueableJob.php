<?php

namespace App\Jobs\Trial;

use App\Support\Facades\App;
use App\Support\Jobs\QueueableJob as BaseQueueableJob;
use Illuminate\Support\Facades\Log;

class QueueableJob extends BaseQueueableJob
{
    protected function handling(): void
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}
