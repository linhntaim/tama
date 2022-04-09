<?php

namespace App\Jobs;

use App\Support\App;
use App\Support\Jobs\ContinuousBatchJob;
use Illuminate\Support\Facades\Log;

class TrialContinuousBatchJob extends ContinuousBatchJob
{
    protected function batchByIndex(int $batchIndex): iterable
    {
        return $this->batchIndex() < 10 ? range($batchIndex * 10, ($batchIndex + 1) * 10 - 1) : [];
    }

    protected function handleBatchItem($item)
    {
        Log::info(sprintf('Batch_%02d [%02d]: %s', $this->batchIndex(), $item, $date = date_timer()->compound('longDate', ' ', 'longTime')));
        if (App::runningSolelyInConsole()) {
            echo sprintf('Batch_%02d [item_%02d]: %s', $this->batchIndex(), $item, $date) . PHP_EOL;
        }
    }
}