<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse as BaseSuccessfulPasswordResetLinkRequestResponse;

class SuccessfulPasswordResetLinkRequestResponse extends BaseSuccessfulPasswordResetLinkRequestResponse
{
    use Requests, Responses;

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        return $this->advancedRequest()->expectsJson()
            ? $this->responseResource($request, ['message' => trans($this->status)])
            : back()->with('status', trans($this->status));
    }
}
