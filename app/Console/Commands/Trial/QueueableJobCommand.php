<?php

namespace App\Console\Commands\Trial;

use App\Jobs\TrialQueueableJob;
use App\Support\Console\Commands\JobCommand as BaseJobCommand;

class QueueableJobCommand extends BaseJobCommand
{
    protected string $jobClass = TrialQueueableJob::class;
}