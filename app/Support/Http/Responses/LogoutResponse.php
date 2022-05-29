<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Requests;
use App\Support\Http\Responses;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LogoutResponse as BaseLogoutResponse;

class LogoutResponse extends BaseLogoutResponse
{
    use Requests, Responses;

    public function toResponse($request)
    {
        return $this->advancedRequest()->expectsJson()
            ? $this->responseResource($request)
            : redirect(Fortify::redirects('logout', '/'));
    }
}
