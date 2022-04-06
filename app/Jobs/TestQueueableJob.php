<?php

namespace App\Jobs;

use App\Support\App;
use App\Support\Jobs\QueueableJob;

class TestQueueableJob extends QueueableJob
{
    protected function handling()
    {
        if (App::runningSolelyInConsole()) {
            echo date_timer()->compound('longDate', ' ', 'longTime') . PHP_EOL;
        }
    }
}