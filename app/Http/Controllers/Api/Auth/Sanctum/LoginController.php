<?php

namespace App\Http\Controllers\Api\Auth\Sanctum;

use App\Http\Controllers\Api\Auth\LoginController as BaseLoginController;
use App\Support\Http\Resources\SanctumAccessTokenResource;
use App\Support\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\NewAccessToken;

class LoginController extends BaseLoginController
{
    /**
     * @param Request $request
     * @param User $user
     * @return NewAccessToken
     */
    protected function loginToken(Request $request, User $user): NewAccessToken
    {
        return $user->createToken('login');
    }

    /**
     * @param Request $request
     * @param NewAccessToken $token
     * @return array
     */
    protected function storeResponseAdditional(Request $request, $token): array
    {
        return $this->resourceTransform($token, SanctumAccessTokenResource::class, $request, 'token');
    }
}
