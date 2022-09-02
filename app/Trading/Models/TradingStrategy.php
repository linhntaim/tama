<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property int $buy_trading_id
 * @property string $buy_strategy
 * @property float $buy_risk
 * @property int $sell_trading_id
 * @property float $sell_risk
 *
 * @property float $baseAmount
 * @property float $quoteAmount
 * @property User $user
 * @property Collection<int, TradingSwap> $swaps
 */
class TradingStrategy extends Model
{
    protected $table = 'trading_strategies';

    protected $fillable = [
        'user_id',
        'buy_trading_id',
        'buy_risk',
        'sell_trading_id',
        'sell_risk',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'buy_trading_id' => 'integer',
        'buy_risk' => 'float',
        'sell_trading_id' => 'integer',
        'sell_risk' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function swaps(): HasMany
    {
        return $this->hasMany(TradingSwap::class, 'trading_strategy_id', 'id');
    }

    public function baseAmount(): Attribute
    {
        return Attribute::get(fn() => (float)$this->swaps->sum('base_amount'));
    }

    public function quoteAmount(): Attribute
    {
        return Attribute::get(fn() => (float)$this->swaps->sum('quote_amount'));
    }
}
