<?php

namespace App\Support\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Laravel\Sanctum\NewAccessToken;

/**
 * @mixin NewAccessToken
 */
class SanctumAccessTokenResource extends ModelResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            'access_token' => $this->plainTextToken,
            'expired_at' => ($expiration = config('sanctum.expiration'))
                ? $this->accessToken->created_at
                    ->addSeconds((int)$expiration)
                    ->timestamp
                : null,
        ];
    }
}
