<?php

namespace App\Console\Commands\Trial;

use App\Jobs\Trial\Job as TrialJob;
use App\Support\Console\Commands\JobCommand as BaseJobCommand;

class JobCommand extends BaseJobCommand
{
    protected string $jobClass = TrialJob::class;
}
