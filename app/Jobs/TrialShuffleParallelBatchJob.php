<?php

namespace App\Jobs;

class TrialShuffleParallelBatchJob extends TrialLinearParallelBatchJob
{
    protected bool $useLinearIndices = false;
}