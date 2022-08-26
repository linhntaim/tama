<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\PasswordResetResponse as BasePasswordResetResponse;

class PasswordResetResponse extends BasePasswordResetResponse
{
    use Requests, Responses;

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        return $this->advancedRequest()->expectsJson()
            ? $this->responseResource($request, ['message' => trans($this->status)])
            : redirect(Fortify::redirects('password-reset', route('login')))->with('status', trans($this->status));
    }
}
