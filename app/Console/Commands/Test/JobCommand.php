<?php

namespace App\Console\Commands\Test;

use App\Jobs\TestJob;
use App\Jobs\TestQueueableJob;
use App\Support\Console\Commands\Command;

class JobCommand extends Command
{
    protected function handling(): int
    {
        TestJob::dispatch();
        TestQueueableJob::dispatch();
        return $this->exitSuccess();
    }
}