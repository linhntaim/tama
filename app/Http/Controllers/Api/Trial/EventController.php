<?php

namespace App\Http\Controllers\Api\Trial;

use App\Events\Trial\Event as TrialEvent;
use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends ApiController
{
    public function store(Request $request): JsonResponse
    {
        TrialEvent::dispatch();
        return $this->responseSuccess($request);
    }
}
