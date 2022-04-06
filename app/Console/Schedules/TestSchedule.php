<?php

namespace App\Console\Schedules;

use App\Support\App;
use App\Support\Console\Schedules\Schedule;

class TestSchedule extends Schedule
{
    protected function handling()
    {
        if (App::runningSolelyInConsole()) {
            echo date_timer()->compound('longDate', ' ', 'longTime') . PHP_EOL;
        }
    }
}