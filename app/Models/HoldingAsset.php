<?php

namespace App\Models;

use App\Support\Models\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $exchange
 * @property string $symbol
 * @property float $amount
 */
class HoldingAsset extends Model
{
    protected $table = 'holding_assets';

    protected $fillable = [
        'user_id',
        'exchange',
        'symbol',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    protected $visible = [
        'exchange',
        'symbol',
        'amount',
    ];
}
