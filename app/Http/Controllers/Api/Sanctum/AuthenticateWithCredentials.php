<?php

namespace App\Http\Controllers\Api\Sanctum;

use App\Actions\Fortify\AuthenticateWithCredentials as BaseAuthenticateWithCredentials;

class AuthenticateWithCredentials extends BaseAuthenticateWithCredentials
{
    protected function guard(): string
    {
        return 'sanctum';
    }
}
