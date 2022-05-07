<?php

namespace App\Jobs\Trial;

class ShuffleParallelBatchJob extends LinearParallelBatchJob
{
    protected bool $useLinearIndices = false;
}
