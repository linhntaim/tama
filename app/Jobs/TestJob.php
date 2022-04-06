<?php

namespace App\Jobs;

use App\Support\App;
use App\Support\Jobs\Job;

class TestJob extends Job
{
    protected function handling()
    {
        if (App::runningSolelyInConsole()) {
            echo date_timer()->compound('longDate', ' ', 'longTime') . PHP_EOL;
        }
    }
}