<?php

namespace App\Support\Models;

use App\Support\Auth\Concerns\MustWelcomeEmail;
use App\Support\Mail\Contracts\ProvidesEmailAddress;
use App\Support\Notifications\Concerns\Notifiable;
use App\Support\Notifications\Contracts\Notifiable as NotifiableContract;
use App\Support\Notifications\Contracts\Notifier as NotifierContract;
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
                                             NotifiableContract,
                                             NotifierContract,
                                             ProvidesEmailAddress
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable, MustWelcomeEmail, MustVerifyEmail;

    public static function hashPassword($password): string
    {
        return Hash::make($password);
    }

    public string $rawPassword;

    public array $uniques = ['email'];

    public function setRawPassword(string $rawPassword): static
    {
        $this->rawPassword = $rawPassword;
        return $this;
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: static fn($value) => static::hashPassword($value),
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
}
