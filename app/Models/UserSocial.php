<?php

namespace App\Models;

use App\Support\Models\Model;

/**
 * @property string $provider_id
 */
class UserSocial extends Model
{
    protected $table = 'user_socials';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
    ];
}
