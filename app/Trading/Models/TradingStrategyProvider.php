<?php

namespace App\Trading\Models;

use App\Support\Models\ModelProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TradingStrategyProvider extends ModelProvider
{
    public string $modelClass = TradingStrategy::class;

    protected function whereByTrading(Builder $query, int|Trading $trading): Builder
    {
        return $query->where(function ($query) use ($trading) {
            $tradingId = $this->retrieveKey($trading);
            $query->where('buy_trading_id', $tradingId)
                ->orWhere('sell_trading_id', $tradingId);
        });
    }

    public function allByTrading(int|Trading $trading): Collection
    {
        return $this->all(['trading' => $trading]);
    }
}
