<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use App\Support\Models\QueryValues\LikeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method TradingStrategy createWithAttributes(array $attributes = [])
 * @method TradingStrategy|null first(array $conditions = [])
 * @method TradingStrategy|null firstByKey(int|string $key)
 */
class TradingStrategyProvider extends ModelProvider
{
    public string $modelClass = TradingStrategy::class;

    protected function whereByTrading(Builder $query, int|Trading|LikeValue $trading): Builder
    {
        return $query->where(function ($query) use ($trading) {
            if ($trading instanceof LikeValue) {
                $query
                    ->whereHas('buyTrading', function ($query) use ($trading) {
                        $query->where('slug', 'like', (string)$trading);
                    })
                    ->orWhereHas('sellTrading', function ($query) use ($trading) {
                        $query->where('slug', 'like', (string)$trading);
                    });
            }
            else {
                $tradingId = $this->retrieveKey($trading);
                $query->where('buy_trading_id', $tradingId)
                    ->orWhere('sell_trading_id', $tradingId);
            }
        });
    }

    public function paginationByUser(int|User $user, ?string $keyword = null, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->pagination(array_filter([
            'user_id' => $this->retrieveKey($user),
            'trading' => is_null($keyword) ? null : LikeValue::create($keyword),
        ]), $perPage, $page);
    }

    public function allActiveByTrading(int|Trading $trading): Collection
    {
        return $this->all([
            'trading' => $trading,
            'status' => TradingStrategy::STATUS_ACTIVE,
        ]);
    }
}
