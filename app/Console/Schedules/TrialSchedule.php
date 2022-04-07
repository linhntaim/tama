<?php

namespace App\Console\Schedules;

use App\Support\App;
use App\Support\Console\Schedules\Schedule;
use Illuminate\Support\Facades\Log;

class TrialSchedule extends Schedule
{
    protected function handling()
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}