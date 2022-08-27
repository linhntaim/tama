<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $bot
 * @property string $exchange
 * @property string $ticker
 * @property string $interval
 * @property array $options
 * @property User[]|Collection $subscribers
 */
class Trading extends Model
{
    protected $table = 'tradings';

    protected $fillable = [
        'slug',
        'bot',
        'exchange',
        'ticker',
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
}
