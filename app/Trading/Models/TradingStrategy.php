<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $buy_trading_id
 * @property string $buy_strategy
 * @property int $sell_trading_id
 * @property string $sell_strategy
 *
 * @property User $user
 * @property Collection<int, TradingSwap> $swaps
 */
class TradingStrategy extends Model
{
    protected $table = 'trading_strategies';

    protected $fillable = [
        'user_id',
        'buy_trading_id',
        'buy_strategy',
        'sell_trading_id',
        'sell_strategy',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'buy_trading_id' => 'integer',
        'sell_trading_id' => 'integer',
    ];

    protected function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function swaps(): HasMany
    {
        return $this->hasMany(TradingSwap::class, 'trading_strategy_id', 'id');
    }
}
