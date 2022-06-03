<?php

namespace App\Support\Trading\Strategies\Model;

use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $strategy_id
 * @property string $name
 * @property float $score
 * @property int $type
 * @property bool $isBullish
 * @property bool $isBearish
 */
class StrategySignal extends Model
{
    public const TYPE_BULLISH = 1;
    public const TYPE_BEARISH = 2;

    protected $table = 'trading_strategy_signals';

    protected $fillable = [
        'strategy_id',
        'name',
        'score',
        'type',
    ];

    protected $casts = [
        'score' => 'float',
        'type' => 'integer',
    ];

    public function isBullish(): Attribute
    {
        return Attribute::get(fn() => $this->type == self::TYPE_BULLISH);
    }

    public function isBearish(): Attribute
    {
        return Attribute::get(fn() => $this->type == self::TYPE_BEARISH);
    }
}
