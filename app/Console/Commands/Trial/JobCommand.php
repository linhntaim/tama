<?php

namespace App\Console\Commands\Trial;

use App\Jobs\TrialJob;
use App\Support\Console\Commands\JobCommand as BaseJobCommand;

class JobCommand extends BaseJobCommand
{
    protected string $jobClass = TrialJob::class;
}