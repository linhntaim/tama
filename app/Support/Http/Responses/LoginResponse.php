<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Responses;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    use Responses;

    public function toResponse($request)
    {
        return $request->expectsJson()
            ? $this->responseResource($request, ['two_factor' => false])
            : redirect()->intended(Fortify::redirects('login'));
    }
}
