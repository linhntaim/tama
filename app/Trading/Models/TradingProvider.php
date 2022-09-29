<?php

namespace App\Trading\Models;

use App\Models\User;
use App\Support\Models\Model;
use App\Support\Models\ModelProvider;
use App\Support\Models\QueryValues\HasValue;
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

    public function paginationByUser(int|User $user, ?string $keyword = null, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->pagination(array_filter([
            'users' => function ($query) use ($user) {
                $query->where('id', $this->retrieveKey($user));
            },
            'slug' => is_null($keyword) ? null : new LikeValue($keyword),
        ]), $perPage, $page);
    }

    public function allByRunning(string|array|null $exchange = null, string|array|null $ticker = null, string|array|null $interval = null): Collection
    {
        return $this->all(array_filter([
            'exchange' => $exchange,
            'ticker' => $ticker,
            'interval' => $interval,
            'subscribers' => new HasValue(),
        ]));
    }
}
