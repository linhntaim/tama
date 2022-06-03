<?php

namespace App\Http\Controllers\Api\Auth\Sanctum;

use App\Support\Http\Controllers\Api\Auth\AuthenticatedTokenController as BaseAuthenticatedTokenController;
use App\Support\Http\Resources\SanctumAccessTokenResource;
use Illuminate\Http\Request;

class AuthenticatedTokenController extends BaseAuthenticatedTokenController
{
    protected string $tokenResourceClass = SanctumAccessTokenResource::class;

    #region Login
    protected function loginPipes(): array
    {
        return [
            AuthenticateWithCredentials::class,
        ];
    }
    #endregion

    #region Logout
    public function destroyAllTokens(Request $request)
    {
        $request->user()->tokens()->delete();
    }

    public function destroyToken(Request $request, $token)
    {
        $request->user()->tokens()
            ->where('id', $token)
            ->delete();
    }

    protected function currentToken(Request $request): mixed
    {
        return $request->user()->currentAccessToken()->id;
    }
    #endregion
}
