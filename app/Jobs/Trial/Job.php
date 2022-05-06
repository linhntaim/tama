<?php

namespace App\Jobs\Trial;

use App\Support\Facades\App;
use App\Support\Jobs\Job as BaseJob;
use Illuminate\Support\Facades\Log;

class Job extends BaseJob
{
    protected function handling()
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}
