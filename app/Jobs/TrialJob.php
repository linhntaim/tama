<?php

namespace App\Jobs;

use App\Support\App;
use App\Support\Jobs\Job;
use Illuminate\Support\Facades\Log;

class TrialJob extends Job
{
    protected function handling()
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}