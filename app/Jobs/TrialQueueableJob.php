<?php

namespace App\Jobs;

use App\Support\App;
use App\Support\Jobs\QueueableJob;
use Illuminate\Support\Facades\Log;

class TrialQueueableJob extends QueueableJob
{
    protected function handling()
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}