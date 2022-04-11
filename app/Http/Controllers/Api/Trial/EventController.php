<?php

namespace App\Http\Controllers\Api\Trial;

use App\Events\TrialEvent;
use App\Support\Http\Controllers\ApiController;
use App\Support\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        TrialEvent::dispatch();
        return $this->responseSuccess($request);
    }
}