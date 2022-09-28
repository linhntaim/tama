<?php

namespace App\Trading\Models;

use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use App\Support\Models\QueryValues\LikeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Trading createWithAttributes(array $attributes = [])
 * @method Trading|null first(array $conditions = [])
 * @method Trading|null firstByKey(int|string $key)
 * @method Trading|null firstByUnique(int|string $unique)
 * @method Collection all(array $conditions = [])
 */
class TradingProvider extends ModelProvider
{
    public string $modelClass = Trading::class;

    public function firstBySlug(string $slug): ?Trading
    {
        return $this->first(['slug' => $slug]);
    }

    protected function whereBySubscriber(Builder $query, $subscriber): Builder
    {
        return $query->whereHas('subscribers', function ($query) use ($subscriber) {
            $query->where('id', $this->retrieveKey($subscriber));
        });
    }

    protected function whereByRunning(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->has('subscribers')
                ->orHas('buyStrategies')
                ->orHas('sellStrategies');
        });
    }

    public function paginationBySubscriber($subscriber, ?string $keyword = null, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->pagination(array_filter([
            'subscriber' => $subscriber,
            'slug' => is_null($keyword) ? null : new LikeValue($keyword),
        ]), $perPage, $page);
    }

    public function allByRunning(string|array|null $exchange = null, string|array|null $ticker = null, string|array|null $interval = null): Collection
    {
        return $this->all(array_filter([
            'exchange' => $exchange,
            'ticker' => $ticker,
            'interval' => $interval,
            'running' => true,
        ]));
    }
}
