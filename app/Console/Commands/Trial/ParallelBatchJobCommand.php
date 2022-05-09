<?php

namespace App\Console\Commands\Trial;

use App\Jobs\Trial\LinearParallelBatchJob as TrialLinearParallelBatchJob;
use App\Jobs\Trial\ShuffleParallelBatchJob as TrialShuffleParallelBatchJob;
use App\Support\Console\Commands\JobCommand as BaseJobCommand;

class ParallelBatchJobCommand extends BaseJobCommand
{
    public $signature = '{--shuffle}';

    protected function getJobClass(): string
    {
        return $this->option('shuffle')
            ? TrialShuffleParallelBatchJob::class
            : TrialLinearParallelBatchJob::class;
    }
}
