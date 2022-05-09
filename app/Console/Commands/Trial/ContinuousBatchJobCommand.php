<?php

namespace App\Console\Commands\Trial;

use App\Jobs\Trial\ContinuousBatchJob as TrialContinuousBatchJob;
use App\Support\Console\Commands\JobCommand as BaseJobCommand;

class ContinuousBatchJobCommand extends BaseJobCommand
{
    protected string $jobClass = TrialContinuousBatchJob::class;
}
