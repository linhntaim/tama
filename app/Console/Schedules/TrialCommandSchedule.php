<?php

namespace App\Console\Schedules;

use App\Support\Console\Schedules\CommandSchedule;

class TrialCommandSchedule extends CommandSchedule
{
    protected string $command = 'trial:command';
}