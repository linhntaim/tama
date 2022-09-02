<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $bot
 * @property string $exchange
 * @property string $ticker
 * @property string $interval
 * @property array $options
 * @property User[]|Collection $subscribers
 * @property TradingStrategy[]|Collection $buyingStrategies
 * @property TradingStrategy[]|Collection $sellingStrategies
 */
class Trading extends Model
{
    protected $table = 'tradings';

    protected $fillable = [
        'slug',
        'bot',
        'exchange',
        'ticker',
        'base_symbol',
        'quote_symbol',
        'interval',
        'options',
    ];

    public array $uniques = [
        'id',
        'slug',
    ];

    protected $casts = [
        'id' => 'integer',
        'options' => 'array',
    ];

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'trading_subscribers', 'trading_id', 'user_id');
    }

    public function buyingStrategies(): HasMany
    {
        return $this->hasMany(TradingStrategy::class, 'buy_trading_id', 'id');
    }

    public function sellingStrategies(): HasMany
    {
        return $this->hasMany(TradingStrategy::class, 'sell_trading_id', 'id');
    }
}
