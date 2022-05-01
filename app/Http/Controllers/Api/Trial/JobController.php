<?php

namespace App\Http\Controllers\Api\Trial;

use App\Jobs\TrialJob;
use App\Jobs\TrialQueueableJob;
use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;

class JobController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        TrialJob::dispatch();
        TrialQueueableJob::dispatch();
        return $this->responseSuccess($request);
    }
}