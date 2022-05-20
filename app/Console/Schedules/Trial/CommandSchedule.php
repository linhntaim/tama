<?php

namespace App\Console\Schedules\Trial;

use App\Support\Console\Schedules\CommandSchedule as BaseCommandSchedule;

class CommandSchedule extends BaseCommandSchedule
{
    protected string $command = 'trial:command';
}
