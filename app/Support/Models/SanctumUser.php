<?php

namespace App\Support\Models;

use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

abstract class SanctumUser extends User implements IHasApiTokens
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
