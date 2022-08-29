<?php

namespace App\Trading\Models;

use App\Support\Models\Model;

/**
 * @property int $id
 * @property int $trading_strategy_id
 * @property int $trading_id
 * @property float $base_amount
 * @property float $quote_amount
 */
class TradingSwap extends Model
{
    protected $table = 'trading_swaps';

    protected $fillable = [
        'trading_strategy_id',
        'trading_id',
        'base_amount',
        'quote_amount',
    ];

    protected $casts = [
        'id' => 'integer',
        'trading_strategy_id' => 'integer',
        'trading_id' => 'integer',
        'base_amount' => 'float',
        'quote_amount' => 'float',
    ];
}
