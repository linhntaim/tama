<?php

namespace App\Console\Schedules\Trial;

use App\Support\Console\Schedules\Schedule as BaseSchedule;
use App\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class Schedule extends BaseSchedule
{
    protected function handling()
    {
        Log::info($date = date_timer()->compound('longDate', ' ', 'longTime'));
        if (App::runningSolelyInConsole()) {
            echo $date . PHP_EOL;
        }
    }
}
