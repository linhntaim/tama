<?php

namespace App\Http\Controllers\Api\Auth\Sanctum;

use App\Http\Controllers\Api\Auth\LogoutController as BaseLogoutController;
use Illuminate\Http\Request;

class LogoutController extends BaseLogoutController
{
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
}
