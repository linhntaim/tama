<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Support\Http\Controllers\Api\AuthenticatedTokenController as BaseAuthenticatedTokenController;

class AuthenticatedTokenController extends BaseAuthenticatedTokenController
{
    protected function loginPipes(): array
    {
        return [
            AuthenticateWithCredentials::class,
        ];
    }
}
