<?php

namespace App\Support\Models;

use App\Support\Database\Concerns\DatabaseTransaction;
use App\Support\Models\Contracts\HasProtected as HasProtectedContract;
use App\Support\Models\QueryConditions\GroupCondition;
use App\Support\Models\QueryConditions\LimitCondition;
use App\Support\Models\QueryConditions\QueryCondition;
use App\Support\Models\QueryConditions\SelectCondition;
use App\Support\Models\QueryConditions\SortCondition;
use App\Support\Models\QueryConditions\WhereCondition;
use App\Support\Models\QueryConditions\WithCondition;
use App\Support\Models\QueryValues\HasValue;
use App\Support\Models\QueryValues\LikeValue;
use App\Support\Models\QueryValues\NotNullValue;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @method int lock(int $newValue)
 * @method bool force(bool $newValue)
 * @method bool strict(bool $newValue)
 * @method bool pinned(bool $newValue)
 * @method bool protected (bool $newValue)
 * @method array wheres(array $newValue)
 */
abstract class ModelProvider
{
    use DatabaseTransaction;

    private const LOCK_NONE = 0;
    private const LOCK_UPDATE = 1;
    private const LOCK_SHARED = 2;

    public string $modelClass;

    protected ?Model $model = null;

    protected ?bool $useSoftDeletes = null;

    protected int $perPage;

    protected int $lock = self::LOCK_NONE;

    protected bool $force = false;

    protected bool $strict = true;

    protected bool $pinned = false;

    protected bool $protected = true;

    /**
     * @var QueryCondition[]
     */
    protected array $wheres = [];

    protected ?int $read = null;

    protected int $perRead = 1000;

    protected ?Builder $queryRead = null;

    protected ?int $write = null;

    protected ?bool $writeStrict = null;

    protected ?array $writes = null;

    protected int $perWrite = 1000;

    public function __construct(Model|callable|int|string $model = null)
    {
        if (!is_a($this->modelClass, Model::class, true)) {
            throw new RuntimeException("Class [$this->modelClass] is not a model class.");
        }

        $this->model($model);
    }

    public function lockForUpdate(): static
    {
        $this->lock = self::LOCK_UPDATE;
        return $this;
    }

    public function sharedLock(): static
    {
        $this->lock = self::LOCK_SHARED;
        return $this;
    }

    public function forced(): static
    {
        $this->force = true;
        return $this;
    }

    public function notStrict(): static
    {
        $this->strict = false;
        return $this;
    }

    public function pinModel(): static
    {
        $this->pinned = true;
        return $this;
    }

    public function skipProtected(): static
    {
        $this->protected = false;
        return $this;
    }

    public function with(string|array $relations, string|Closure|null $callback = null): static
    {
        $this->wheres[] = new WithCondition(
            match (true) {
                $callback instanceof Closure => [$relations => $callback],
                is_string($relations) => func_get_args(),
                default => $relations,
            }
        );
        return $this;
    }

    public function select(array|string $column = '*', string ...$columns): static
    {
        $this->wheres[] = new SelectCondition(is_array($column) ? $column : func_get_args());
        return $this;
    }

    public function sort(string|Closure|Expression $by, bool $ascending = true): static
    {
        $this->wheres[] = new SortCondition($by, $ascending);
        return $this;
    }

    public function group(array|string $column, string ...$columns): static
    {
        $this->wheres[] = new GroupCondition(is_array($column) ? $column : func_get_args());
        return $this;
    }

    public function limit(int $limit, int $skip = 0): static
    {
        if ($limit > 0) {
            $this->wheres[] = new LimitCondition($limit, $skip);
        }
        return $this;
    }

    public function more(string $by, bool $ascending = true, $pivot = null): static
    {
        $this->sort($by, $ascending);
        if (!is_null($pivot)) {
            $this->wheres[] = new WhereCondition($by, $pivot);
        }
        return $this;
    }

    /**
     * @param QueryCondition|QueryCondition[] $condition
     * @param QueryCondition ...$conditions
     * @return static
     */
    public function condition(QueryCondition|array $condition, QueryCondition ...$conditions): static
    {
        array_push($this->wheres, ...(is_array($condition) ? $condition : func_get_args()));
        $this->wheres[] = $condition;
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if (property_exists($this, $name)) {
            return take($this->{$name}, function () use ($name, $arguments) {
                $this->{$name} = $arguments[0] ?? null;
            });
        }

        throw new RuntimeException('Method does not exist.');
    }

    public function useSoftDeletes(): bool
    {
        return $this->useSoftDeletes
            ?? ($this->useSoftDeletes = class_use($this->modelClass, SoftDeletes::class));
    }

    public function perPage(): int
    {
        return $this->perPage ?? ($this->perPage = $this->newModel()->getPerPage());
    }

    public function model(Model|callable|int|string $model = null, bool $byUnique = true): ?Model
    {
        if (is_callable($model)) {
            $model = $model();
        }
        if (!is_null($model)) {
            if ($model instanceof $this->modelClass) {
                $this->model = $model;
            }
            else {
                $this->model = $byUnique ? $this->firstByUnique($model) : $this->firstByKey($model);
            }
        }
        return $this->model;
    }

    public function current(): ?Model
    {
        return $this->model;
    }

    public function key(): int|string
    {
        return $this->current()?->getKey();
    }

    public function withModel(mixed $model = null): static
    {
        $this->model($model);
        return $this;
    }

    public function refreshModel(): Model
    {
        return $this->hasModel() ? $this->model->refresh() : $this->model;
    }

    public function hasModel(): bool
    {
        return !is_null($this->model) && $this->model->exists;
    }

    public function newModel(bool $pinned = false): Model
    {
        $modelClass = $this->modelClass;
        return $pinned ? ($this->model = new $modelClass) : new $modelClass;
    }

    public function newQuery(): Builder
    {
        return $this->newModel()->newQuery();
    }

    public function query(): Builder
    {
        return $this->newQuery();
    }

    public function newModelQuery(): Builder
    {
        return $this->newModel()->newModelQuery();
    }

    public function modelQuery(): Builder
    {
        return $this->newModelQuery();
    }

    public function createWithAttributes(array $attributes = []): Model
    {
        return $this->model = $this->newQuery()->create($attributes);
    }

    public function firstOrCreateWithAttributes(array $attributes = [], array $values = []): Model
    {
        return $this->model = $this->catchProtectedModel(
            $this->newQuery()->firstOrCreate($attributes, $values)
        );
    }

    public function updateWithAttributes(array $attributes = []): Model
    {
        $this->catchProtectedModel($this->model);
        $this->model->update($attributes);
        return $this->model;
    }

    public function updateOrCreateWithAttributes(array $attributes, array $values = []): Model
    {
        return $this->model = take($this->newQuery()->firstOrNew($attributes), function (Model $instance) use ($values) {
            if ($instance->exists) {
                $this->catchProtectedModel($instance);
            }
            $instance->fill($values)->save();
        });
    }

    protected function executeDelete(Builder|Model $query): bool
    {
        $this->force(false) && $this->useSoftDeletes()
            ? $query->forceDelete()
            : $query->delete();
        return true;
    }

    protected function catchProtectedModel($model, string $message = 'Cannot modify protected model.'): ?Model
    {
        if ($model instanceof HasProtectedContract
            && $model->isProtected()
            && $this->protected(true)) {
            throw new RuntimeException($message);
        }
        return $model;
    }

    public function delete(): bool
    {
        return $this->executeDelete(
            $this->catchProtectedModel($this->model)
        );
    }

    protected function queryLock(Builder $query): Builder
    {
        return match ($this->lock(self::LOCK_NONE)) {
            self::LOCK_UPDATE => $query->lockForUpdate(),
            self::LOCK_SHARED => $query->sharedLock(),
            default => $query,
        };
    }

    protected function executeAll(Builder $query): EloquentCollection
    {
        return $this->queryLock($query)->get();
    }

    protected function executePagination(Builder $query, ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $query->paginate($perPage ?: $this->perPage(), ['*'], 'page', $page);
    }

    protected function executeFirst(Builder $query): ?Model
    {
        $model = $this->strict(true)
            ? $this->queryLock($query)->firstOrFail()
            : $this->queryLock($query)->first();
        return $this->pinned(false) ? ($this->model = $model) : $model;
    }

    protected function executeCount(Builder $query, string $columns = '*'): int
    {
        return $query->count($columns);
    }

    protected function executeMax(Builder $query, string $column): mixed
    {
        return $query->max($column);
    }

    public function protectedQuery(): Builder
    {
        $old = $this->pinned;
        $model = $this->newModel();
        $this->pinned = $old;
        if ($model instanceof HasProtectedContract && $this->protected(true)) {
            return $this->query()->whereNotIn($model->getProtectedKey(), $model->getProtectedValues());
        }
        return $this->query();
    }

    public function whereQuery(): Builder
    {
        $query = $this->protectedQuery();
        foreach ($this->wheres([]) as $queryCondition) {
            $queryCondition($query);
        }
        return $query;
    }

    public function queryWhere(array $conditions = []): Builder
    {
        $query = $this->whereQuery();
        foreach ($conditions as $column => $value) {
            switch (true) {
                case is_int($column):
                    switch (true) {
                        case is_array($value):
                            if (count($value) > 0) {
                                switch (true) {
                                    case isset($value['column']):
                                        $query->where(function ($query) use ($value) {
                                            $query->where(
                                                $value['column'],
                                                $value['operator'] ?? '=',
                                                $value['value'] ?? null,
                                                $value['boolean'] ?? 'and',
                                            );
                                        });
                                        break;
                                    case isset($value[0]):
                                        $query->where(function ($query) use ($value) {
                                            $query->where($value[0], $value[1] ?? null, $value[2] ?? null, $value[3] ?? 'and');
                                        });
                                        break;
                                    default:
                                        $query->where($value);
                                        break;
                                }
                            }
                            break;
                        case is_callable($value):
                            /**
                             * @see QueryCondition
                             */
                            $value($query);
                            break;
                    }
                    break;
                case is_string($column):
                    switch (true) {
                        case method_exists($this, $method = 'whereBy' . Str::studly($column)):
                            $this->{$method}($query, $value, $conditions);
                            break;
                        case is_null($value):
                            $query->whereNull($column);
                            break;
                        case $value instanceof NotNullValue:
                            $query->whereNotNull($column);
                            break;
                        case $value instanceof LikeValue:
                            $query->where($column, 'like', (string)$value);
                            break;
                        case $value instanceof HasValue:
                            $query->has($column, $value->getOperator(), $value->getCount(), 'and', $value->getCallback());
                            break;
                        case is_array($value):
                            $query->whereIn($column, $value);
                            break;
                        case is_callable($value):
                            $query->whereHas($column, $value);
                            break;
                        default:
                            $query->where($column, $value);
                            break;
                    }
                    break;
            }
        }
        return $query;
    }

    public function all(array $conditions = []): EloquentCollection
    {
        return $this->executeAll($this->queryWhere($conditions));
    }

    public function readStart(int $perRead = 1000): static
    {
        $this->readEnd();

        $this->read = 0;
        $this->perRead = $perRead;
        return $this;
    }

    public function readQueryPrepare(array $conditions = []): static
    {
        $this->queryRead = $this->queryWhere($conditions);
        return $this;
    }

    public function readQueryClear(): static
    {
        $this->queryRead = null;
        return $this;
    }

    public function read(bool &$more = true): EloquentCollection
    {
        $more = true;
        ++$this->read;
        if (($all = $this->executeAll(
                (clone $this->queryRead)
                    ->skip(($this->read - 1) * $this->perRead)
                    ->take($this->perRead + 1)
            ))->count() > $this->perRead) {
            $all->pop();
        }
        else {
            $more = false;
            $this->readEnd();
        }
        return $all;
    }

    public function readEnd(): static
    {
        $this->read = null;
        return $this->readQueryClear();
    }

    public function writeStart(int $perWrite = 1000, bool $ignore = false): static
    {
        $this->writeEnd();

        $this->writeStrict = !$ignore;
        $this->write = 0;
        $this->writes = [];
        $this->perWrite = $perWrite;
        return $this;
    }

    protected function writeRestart(): static
    {
        $this->write = 0;
        $this->writes = [];
        return $this;
    }

    protected function writeMany(): static
    {
        if (count($this->writes ?? [])) {
            $this->writeStrict
                ? $this->newQuery()->insert($this->writes)
                : $this->newQuery()->insertOrIgnore($this->writes);
        }
        return $this;
    }

    public function write(array $attributes): static
    {
        ++$this->write;
        $this->writes[] = $attributes;
        if ($this->write === $this->perWrite) {
            return $this->writeMany()->writeRestart();
        }
        return $this;
    }

    public function writeEnd(): static
    {
        $this->writeMany();

        $this->write = null;
        $this->writes = null;
        return $this;
    }

    public function next(array $conditions = [], ?int $perPage = null, &$hasMore = false): EloquentCollection
    {
        $perPage = $perPage ?: $this->perPage();
        $collection = $this->firstPagination($conditions, $perPage + 1);
        if ($collection->count() > $perPage) {
            $collection->pop();
            $hasMore = true;
        }
        else {
            $hasMore = false;
        }
        return $collection;
    }

    public function pagination(array $conditions = [], ?int $perPage = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->executePagination($this->queryWhere($conditions), $perPage, $page);
    }

    public function firstPagination(array $conditions = [], ?int $perPage = null): EloquentCollection
    {
        $conditions[] = new LimitCondition($perPage ?: $this->perPage());
        return $this->executeAll($this->queryWhere($conditions));
    }

    public function first(array $conditions = []): ?Model
    {
        return $this->executeFirst($this->queryWhere($conditions));
    }

    public function count(array $conditions = [], string $columns = '*'): int
    {
        return $this->executeCount($this->queryWhere($conditions), $columns);
    }

    public function max(string $column, array $conditions = []): mixed
    {
        return $this->executeMax($this->queryWhere($conditions), $column);
    }

    public function has(array $conditions = []): bool
    {
        return $this->count($conditions) > 0;
    }

    public function queryByKey(int|string $key): Builder
    {
        return $this->queryWhere([$this->newModel()->getKeyName() => $key]);
    }

    public function queryByKeys(array $keys): Builder
    {
        return $this->queryWhere([$this->newModel()->getKeyName() => $keys]);
    }

    public function firstByKey(int|string $key): ?Model
    {
        return $this->first([$this->newModel()->getKeyName() => $key]);
    }

    public function firstByUnique(int|string $unique): ?Model
    {
        $uniqueWhere = [];
        foreach ($this->newModel()->uniques as $key) {
            $uniqueWhere[$key] = $unique;
        }
        return $this->first([['column' => $uniqueWhere, 'boolean' => 'or']]);
    }

    public function allByKeys(array $keys): EloquentCollection
    {
        return $this->all([$this->newModel()->getKeyName() => $keys]);
    }

    public function deleteAll(array $conditions = []): bool
    {
        return $this->executeDelete($this->queryWhere($conditions));
    }

    public function deleteByKey(int|string $key): bool
    {
        return $this->deleteAll([$this->newModel()->getKeyName() => $key]);
    }

    public function deleteByKeys(array $keys): bool
    {
        return $this->deleteAll([$this->newModel()->getKeyName() => $keys]);
    }

    public function generateUniqueValue($column, int|Closure|null $length = null): string
    {
        if (is_null($length)) {
            $callback = method_exists($this, $method = 'makeUnique' . Str::studly($column))
                ? fn() => $this->{$method}()
                : static fn() => Str::random(40);
        }
        elseif (is_int($length)) {
            $callback = static fn() => Str::random($length);
        }
        else {
            $callback = $length;
        }
        while (($unique = $callback()) && $this->has([new WhereCondition($column, $unique)])) {
        }
        return $unique;
    }

    public function retrieveKey($model)
    {
        return $model instanceof Model ? $model->getKey() : $model;
    }

    public function retrieveKeys($models): array
    {
        if (is_array($models)) {
            $models = collect($models);
        }
        return $models
            ->map(function ($model) {
                return $this->retrieveKey($model);
            })
            ->all();
    }
}
