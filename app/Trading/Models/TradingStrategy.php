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
 * @property string $baseAmount
 * @property string $quoteAmount
 * @property bool $isFake
 * @property User $user
 * @property Trading $buyTrading
 * @property Trading $sellTrading
 * @property Collection<int, TradingSwap> $swaps
 * @property Collection<int, TradingSwap> $orderedSwaps
 * @property Collection<int, TradingSwap> $tradeSwaps
 * @property Collection<int, TradingSwap> $buySwaps
 * @property Collection<int, TradingSwap> $sellSwaps
 * @property TradingSwap|null $firstSwap
 * @property string $firstPrice
 * @property TradingSwap|null $lastSwap
 * @property string $lastPrice
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

    public function tradeSwaps(): Attribute
    {
        return Attribute::get(fn(): Collection => $this->orderedSwaps->filter(
            fn(TradingSwap $swap): bool => num_lt($swap->quote_amount, 0) || num_lt($swap->base_amount, 0)
        ));
    }

    public function buySwaps(): Attribute
    {
        return Attribute::get(fn(): Collection => $this->orderedSwaps->filter(
            fn(TradingSwap $swap): bool => num_lt($swap->quote_amount, 0)
        ));
    }

    public function sellSwaps(): Attribute
    {
        return Attribute::get(fn(): Collection => $this->orderedSwaps->filter(
            fn(TradingSwap $swap): bool => num_lt($swap->base_amount, 0)
        ));
    }

    public function firstSwap(): Attribute
    {
        return Attribute::get(fn(): ?TradingSwap => $this->orderedSwaps->first());
    }

    public function firstPrice(): Attribute
    {
        return Attribute::get(fn(): string => $this->firstSwap?->price ?: 0);
    }

    public function lastSwap(): Attribute
    {
        return Attribute::get(fn(): ?TradingSwap => $this->orderedSwaps->last());
    }

    public function lastPrice(): Attribute
    {
        return Attribute::get(fn(): string => $this->lastSwap?->price ?: 0);
    }

    public function baseAmount(): Attribute
    {
        return Attribute::get(fn(): string => with(0, function (string $amount): string {
            $this->swaps->each(function (TradingSwap $swap) use (&$amount) {
                $amount = num_add($amount, $swap->base_amount);
            });
            return $amount;
        }));
    }

    public function quoteAmount(): Attribute
    {
        return Attribute::get(fn(): string => with(0, function (string $amount): string {
            $this->swaps->each(function (TradingSwap $swap) use (&$amount) {
                $amount = num_add($amount, $swap->quote_amount);
            });
            return $amount;
        }));
    }

    public function equivalentQuoteAmount(): Attribute
    {
        return Attribute::get(fn(): string => $this->calculateEquivalentQuoteAmount());
    }

    public function isFake(): Attribute
    {
        return Attribute::get(fn(): bool => $this->type === self::TYPE_FAKE);
    }

    public function calculateEquivalentQuoteAmount(?string $price = null): string
    {
        return num_add(num_mul($price ?: $this->lastPrice, $this->baseAmount), $this->quoteAmount);
    }
}
