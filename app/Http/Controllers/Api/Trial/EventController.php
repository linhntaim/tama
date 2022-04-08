<?php

namespace App\Http\Controllers\Api\Trial;

use App\Events\TrialEvent;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

class EventController extends ApiController
{
    public function store(): JsonResponse
    {
        TrialEvent::dispatch();
        return $this->responseJsonSuccess();
    }
}