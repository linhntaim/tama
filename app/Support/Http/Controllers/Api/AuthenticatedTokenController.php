<?php

namespace App\Support\Http\Controllers\Api;

use App\Support\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Routing\Pipeline;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Http\Requests\LoginRequest;

abstract class AuthenticatedTokenController extends ApiController
{
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return app(LoginResponse::class);
        });
    }

    protected function loginPipeline(LoginRequest $request): \Illuminate\Pipeline\Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter($this->loginPipes()));
    }

    protected abstract function loginPipes(): array;

    public function destroy(Request $request): LogoutResponse
    {
        return app(LogoutResponse::class);
    }
}
