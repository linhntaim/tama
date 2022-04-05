<?php

namespace App\Http\Controllers\Api\Test;

use App\Http\Controllers\ApiController;
use App\Jobs\TestJob;
use App\Jobs\TestQueueableJob;
use Illuminate\Http\JsonResponse;

class JobController extends ApiController
{
    public function store(): JsonResponse
    {
        TestJob::dispatch();
        TestQueueableJob::dispatch();
        return $this->responseJsonSuccess();
    }
}