<?php

namespace App\Support\Models;

use App\Notifications\RegistrationEmail;
use App\Support\Mail\IEmailAddress;
use App\Support\Notifications\INotifiable;
use App\Support\Notifications\INotifier;
use App\Support\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Hash;

abstract class User extends Model implements AuthenticatableContract,
                                             AuthorizableContract,
                                             CanResetPasswordContract,
                                             INotifiable,
                                             INotifier,
                                             IEmailAddress
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable, MustVerifyEmail;

    public static function hashPassword($password): string
    {
        return Hash::make($password);
    }

    public array $uniques = ['email'];

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => static::hashPassword($value),
        );
    }

    public function getNotifierKey()
    {
        return $this->getKey();
    }

    public function getNotifierDisplayName(): string
    {
        return $this->name;
    }

    public function getEmailAddress(): string
    {
        return $this->email;
    }

    public function getEmailName(): ?string
    {
        return $this->name;
    }

    public function matchPassword(string $password): bool
    {
        return Hash::check($password, $this->getAuthPassword());
    }

    public function sendEmailRegistrationNotification(string $password)
    {
        $this->notify(new RegistrationEmail($password));
    }
}
