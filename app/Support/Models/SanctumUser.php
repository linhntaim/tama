<?php

namespace App\Support\Models;

use App\Support\Models\Contracts\HasApiTokens as HasApiTokensContract;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

abstract class SanctumUser extends User implements HasApiTokensContract
{
    use HasApiTokens {
        createToken as baseCreateToken;
    }

    protected NewAccessToken $sanctumToken;

    public function createToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        return $this->sanctumToken = $this->baseCreateToken($name, $abilities);
    }

    public function retrieveToken(): NewAccessToken
    {
        return $this->sanctumToken;
    }
}
