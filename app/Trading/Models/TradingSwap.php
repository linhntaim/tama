<?php

namespace App\Trading\Models;

use App\Support\Models\Casts\Serialize;
use App\Support\Models\Model;
use App\Trading\Bots\Exchanges\MarketOrder;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property int $id
 * @property int $trading_strategy_id
 * @property int|null $trading_broadcast_id
 * @property string $time
 * @property string $price
 * @property string $base_amount
 * @property string $quote_amount
 * @property MarketOrder|null $exchange_order
 * @property string $equivalentQuoteAmount
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
        'exchange_order' => Serialize::class,
    ];

    public function equivalentQuoteAmount(): Attribute
    {
        return Attribute::get(fn(): string => $this->calculateEquivalentQuoteAmount());
    }

    public function calculateEquivalentQuoteAmount(?string $price = null): string
    {
        return num_add(num_mul($price ?: $this->price, $this->base_amount), $this->quote_amount);
    }
}
