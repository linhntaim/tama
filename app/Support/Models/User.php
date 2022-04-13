<?php

namespace App\Support\Models;

use App\Support\Mail\IEmailAddress;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    IEmailAddress
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;

    public function getEmailAddress(): string
    {
        return $this->email;
    }

    public function getEmailName(): ?string
    {
        return $this->name;
    }
}