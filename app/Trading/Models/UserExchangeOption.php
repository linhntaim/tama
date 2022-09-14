<?php

namespace App\Trading\Models;

use App\Support\Models\Model;

/**
 * @property string $api_key
 * @property string $api_secret
 * @property array $others
 */
class UserExchangeOption extends Model
{
    protected $table = 'user_exchange_options';

    protected $fillable = [
        'user_id',
        'exchange',
        'api_key',
        'api_secret',
        'others',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'others' => 'array',
    ];
}
