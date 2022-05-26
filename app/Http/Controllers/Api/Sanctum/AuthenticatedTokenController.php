<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Support\Http\Controllers\Api\AuthenticatedTokenController as BaseAuthenticatedTokenController;
use App\Support\Http\Resources\ModelResource;
use App\Support\Http\Resources\ResourceTransformer;
use App\Support\Http\Resources\SanctumAccessTokenResource;
use Laravel\Fortify\Http\Requests\LoginRequest;

class AuthenticatedTokenController extends BaseAuthenticatedTokenController
{
    use ResourceTransformer;

    protected string $modelResourceClass = ModelResource::class;

    protected function loginPipes(): array
    {
        return [
            AuthenticateWithCredentials::class,
        ];
    }

    protected function loginResponse(LoginRequest $request)
    {
        return parent::loginResponse($request)->setJsonDataCallback(
            function ($data) use ($request) {
                $user = $request->user('sanctum');
                return array_merge(
                    $data,
                    [
                        'model' => $this->resourceTransform($user, $this->modelResourceClass, $request),
                        'token' => $this->resourceTransform($user->sanctumToken, SanctumAccessTokenResource::class, $request),
                    ]
                );
            }
        );
    }
}
