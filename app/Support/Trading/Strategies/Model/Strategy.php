<?php

namespace App\Support\Trading\Strategies\Model;

use App\Support\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property float $initial_fund
 * @property float $current_fund
 * @property float $buy_risk
 * @property float $sell_risk
 * @property string $service
 * @property string $executor
 * @property string $base_symbol
 * @property string $quote_symbol
 * @property string $interval
 *
 * @property Collection|StrategySignal[] $signals
 */
class Strategy extends Model
{
    protected $table = 'trading_strategies';

    protected $fillable = [
        'user_id',
        'initial_fund', // on quote symbol
        'current_fund', // on quote symbol
        'buy_risk',
        'sell_risk',
        'service',
        'executor',
        'base_symbol',
        'quote_symbol',
        'interval',
    ];

    protected $casts = [
        'user_id' => 'int',
        'initial_fund' => 'float',
        'current_fund' => 'float',
        'buy_risk' => 'float',
        'sell_risk' => 'float',
    ];

    public function signals(): HasMany
    {
        return $this->hasMany(StrategySignal::class, 'strategy_id');
    }
}
