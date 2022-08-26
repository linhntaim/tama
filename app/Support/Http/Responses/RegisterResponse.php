<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\RegisterResponse as BaseRegisterResponse;

class RegisterResponse extends BaseRegisterResponse
{
    use Requests, Responses;

    public function toResponse($request): JsonResponse|RedirectResponse
    {
        return $this->advancedRequest()->expectsJson()
            ? $this->responseResource($request)
            : redirect()->intended(Fortify::redirects('register'));
    }
}
