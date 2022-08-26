<?php

namespace App\Support\Http\Responses;

use App\Support\Http\Concerns\Requests;
use App\Support\Http\Concerns\Responses;
use Closure;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\LoginResponse as BaseLoginResponse;

class LoginResponse extends BaseLoginResponse
{
    use Requests, Responses;

    protected ?Closure $jsonDataCallback = null;

    public function setJsonDataCallback(Closure $jsonDataCallback): static
    {
        $this->jsonDataCallback = $jsonDataCallback;
        return $this;
    }

    protected function jsonData(): array
    {
        $data = ['two_factor' => false];
        return $this->jsonDataCallback ? call_user_func($this->jsonDataCallback, $data) : $data;
    }

    public function toResponse($request)
    {
        return $this->advancedRequest()->expectsJson()
            ? $this->responseResource($request, $this->jsonData())
            : redirect()->intended(Fortify::redirects('login'));
    }
}
