<?php

namespace App\Http\Controllers\Api\Trial;

use App\Jobs\TrialJob;
use App\Jobs\TrialQueueableJob;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class JobController extends ApiController
{
    public function store(): JsonResponse
    {
        TrialJob::dispatch();
        TrialQueueableJob::dispatch();
        return $this->responseJsonSuccess();
    }
}