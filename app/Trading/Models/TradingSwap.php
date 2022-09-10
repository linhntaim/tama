<?php

namespace App\Trading\Models;

use App\Support\Models\Casts\Serialize;
use App\Support\Models\Model;
use App\Trading\Bots\Exchanges\MarketOrder;

/**
 * @property int $id
 * @property int $trading_strategy_id
 * @property int|null $trading_broadcast_id
 * @property string $time
 * @property float $price
 * @property float $base_amount
 * @property float $quote_amount
 * @property MarketOrder|null $exchange_order
 */
class TradingSwap extends Model
{
    protected $table = 'trading_swaps';

    protected $fillable = [
        'trading_strategy_id',
        'trading_broadcast_id',
        'time',
        'price',
        'base_amount',
        'quote_amount',
        'exchange_order',
    ];

    protected $casts = [
        'id' => 'integer',
        'trading_strategy_id' => 'integer',
        'trading_broadcast_id' => 'integer',
        'price' => 'float',
        'base_amount' => 'float',
        'quote_amount' => 'float',
        'exchange_order' => Serialize::class,
    ];
}
