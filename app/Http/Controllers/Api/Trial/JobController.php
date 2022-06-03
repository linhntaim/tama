<?php

namespace App\Http\Controllers\Api\Trial;

use App\Jobs\Trial\Job as TrialJob;
use App\Jobs\Trial\QueueableJob as TrialQueueableJob;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        TrialJob::dispatch();
        TrialQueueableJob::dispatch();
        return $this->responseSuccess($request);
    }
}
