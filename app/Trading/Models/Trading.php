<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use App\Support\Models\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $bot
 * @property string $exchange
 * @property string $ticker
 * @property string $base_symbol
 * @property string $quote_symbol
 * @property string $interval
 * @property array $options
 * @property User[]|Collection $users
 * @property TradingStrategy[]|Collection $buyStrategies
 * @property TradingStrategy[]|Collection $sellStrategies
 * @property array $botOptions
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

    public function subscribers(): HasMany
    {
        return $this->hasMany(TradingSubscriber::class, 'trading_id', 'id');
    }

    public function users(): MorphToMany
    {
        return $this->morphedByMany(User::class, 'subscribable', 'trading_subscribers', 'trading_id');
    }

    public function buyStrategies(): MorphToMany
    {
        return $this->morphedByMany(TradingStrategy::class, 'subscribable', 'trading_subscribers', 'trading_id')
            ->setMorphClass(BuyStrategy::class);
    }

    public function sellStrategies(): MorphToMany
    {
        return $this->morphedByMany(TradingStrategy::class, 'subscribable', 'trading_subscribers', 'trading_id')
            ->setMorphClass(SellStrategy::class);
    }

    public function botOptions(): Attribute
    {
        return Attribute::get(fn() => array_merge($this->options, [
            'safe_ticker' => true,
            'safe_interval' => true,
        ]));
    }
}
