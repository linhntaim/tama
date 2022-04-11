<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\Models\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property string $name
 * @property string $email
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public static function hashPassword($password): string
    {
        return Hash::make($password);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $visible = [
        'id',
        'name',
        'email',
        'sd_st_email_verified_at',
    ];

    protected $appends = [
        'sd_st_email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public array $uniques = ['email'];

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => static::hashPassword($value),
        );
    }

    protected function sdStEmailVerifiedAt(): Attribute
    {
        return Attribute::make(
            get: fn() => is_null($this->attributes['email_verified_at'])
                ? null
                : date_timer()->compound(
                    'shortDate',
                    ' ',
                    'shortTime',
                    $this->attributes['email_verified_at']
                ),
        );
    }
}
