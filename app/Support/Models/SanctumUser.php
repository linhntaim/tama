<?php

namespace App\Support\Models;

use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class SanctumUser extends User
{
    use HasApiTokens {
        createToken as baseCreateToken;
    }

    public NewAccessToken $sanctumToken;

    public function createToken(string $name, array $abilities = ['*']): NewAccessToken
    {
        return $this->sanctumToken = $this->baseCreateToken($name, $abilities);
    }
}
