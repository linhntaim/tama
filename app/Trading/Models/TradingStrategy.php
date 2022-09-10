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
 * @property int $type
 * @property int $status
 *
 * @property float $baseAmount
 * @property float $quoteAmount
 * @property bool $isFake
 * @property User $user
 * @property Trading $buyTrading
 * @property Trading $sellTrading
 * @property Collection<int, TradingSwap> $swaps
 * @property Collection<int, TradingSwap> $orderedSwaps
 */
class TradingStrategy extends Model
{
    public const TYPE_REAL = 1;
    public const TYPE_FAKE = 2;
    public const STATUS_ACTIVE = 1;
    public const STATUS_PAUSED = 2;

    protected $table = 'trading_strategies';

    protected $fillable = [
        'user_id',
        'buy_trading_id',
        'buy_risk',
        'sell_trading_id',
        'sell_risk',
        'type',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'buy_trading_id' => 'integer',
        'buy_risk' => 'float',
        'sell_trading_id' => 'integer',
        'sell_risk' => 'float',
        'type' => 'integer',
        'status' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function swaps(): HasMany
    {
        return $this->hasMany(TradingSwap::class, 'trading_strategy_id', 'id');
    }

    public function orderedSwaps(): HasMany
    {
        return $this->swaps()->orderBy('time');
    }

    public function buyTrading(): BelongsTo
    {
        return $this->belongsTo(Trading::class, 'buy_trading_id', 'id');
    }

    public function sellTrading(): BelongsTo
    {
        return $this->belongsTo(Trading::class, 'sell_trading_id', 'id');
    }

    public function baseAmount(): Attribute
    {
        return Attribute::get(fn(): float => $this->swaps->sum('base_amount'));
    }

    public function quoteAmount(): Attribute
    {
        return Attribute::get(fn(): float => $this->swaps->sum('quote_amount'));
    }

    public function isFake(): Attribute
    {
        return Attribute::get(fn(): bool => $this->type === self::TYPE_FAKE);
    }
}
