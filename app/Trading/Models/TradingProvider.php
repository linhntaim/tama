<?php

namespace App\Trading\Models;

use App\Support\Models\ModelProvider;
use App\Support\Models\QueryValues\HasValue;
use App\Support\Models\QueryValues\LikeValue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Trading createWithAttributes(array $attributes = [])
 * @method Trading|null first(array $conditions = [])
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

    protected function whereBySubscriber(Builder $query, $subscriber)
    {
        $query->whereHas('subscribers', function ($query) use ($subscriber) {
            $query->where('id', $this->retrieveKey($subscriber));
        });
    }

    public function paginationBySubscriber($subscriber, ?string $keyword = null, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->pagination(array_filter([
            'subscriber' => $subscriber,
            'slug' => is_null($keyword) ? null : LikeValue::create($keyword),
        ]), $perPage, $page);
    }

    public function allByHavingSubscribers(string|array|null $exchange = null, string|array|null $ticker = null, string|array|null $interval = null): Collection
    {
        return $this->all(array_filter([
            'exchange' => $exchange,
            'ticker' => $ticker,
            'interval' => $interval,
            'subscribers' => HasValue::create(),
        ]));
    }
}
