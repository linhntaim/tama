<?php

namespace App\Console\Commands\Trial;

use App\Console\Schedules\TrialSchedule;
use App\Support\Console\Commands\ScheduleCommand as BaseScheduleCommand;

class ScheduleCommand extends BaseScheduleCommand
{
    protected string $scheduleClass = TrialSchedule::class;
}