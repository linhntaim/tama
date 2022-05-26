<?php

namespace App\Support\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Http\Requests\LoginRequest;

abstract class AuthenticatedTokenController extends ApiController
{
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(fn() => $this->loginResponse($request));
    }

    protected function loginPipeline(LoginRequest $request): \Illuminate\Pipeline\Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter($this->loginPipes()));
    }

    protected abstract function loginPipes(): array;

    protected function loginResponse(LoginRequest $request)
    {
        return app(LoginResponseContract::class);
    }

    public function destroy(Request $request): LogoutResponse
    {
        return app(LogoutResponse::class);
    }
}
