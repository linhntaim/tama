<?php

namespace App\Support\ModelProviders;

use App\Support\Exceptions\DatabaseException;
use App\Support\Models\Model;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * @method bool force(bool $newValue)
 * @method bool strict(bool $newValue)
 * @method bool pinned(bool $newValue)
 * @method array wheres(array $newValue)
 */
abstract class ModelProvider
{
    public const PER_PAGE = 10;
    public const SORT_ASC = 'asc';
    public const SORT_DESC = 'desc';

    protected string $modelClass;

    protected ?Model $model = null;

    protected ?bool $modelUseSoftDeletes = null;

    protected bool $force = false;

    protected bool $strict = true;

    protected bool $pinned = false;

    /**
     * @var array|QueryCondition[]
     */
    protected array $wheres = [];

    /**
     * @throws DatabaseException
     */
    public function __construct(Model|callable|int|string $model = null)
    {
        $modelClass = $this->modelClass();
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new RuntimeException("Class [$modelClass] is not a model class.");
        }
        $this->modelClass = $modelClass;

        $this->model($model);
    }

    public abstract function modelClass(): string;

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

    public function sort(string|Closure|Expression $by, string $direction = self::SORT_ASC): static
    {
        $this->wheres[] = new SortCondition($by, $direction);
        return $this;
    }

    public function more(string $by, string $direction = self::SORT_ASC, $pivot = null): static
    {
        $this->sort($by, $direction);
        if (!is_null($pivot)) {
            $this->wheres[] = new WhereCondition($by, $pivot);
        }
        return $this;
    }

    public function __call(string $name, array $arguments)
    {
        if (property_exists($this, $name)) {
            return tap($this->{$name}, function () use ($name, $arguments) {
                $this->{$name} = $arguments[0] ?? null;
            });
        }

        throw new RuntimeException('Method does not exist.');
    }

    public function modelUseSoftDeletes(): bool
    {
        return $this->modelUseSoftDeletes
            ?? ($this->modelUseSoftDeletes = class_use($this->modelClass, SoftDeletes::class));
    }

    /**
     * @throws DatabaseException
     */
    public function model(Model|callable|int|string $model = null): ?Model
    {
        if (is_callable($model)) {
            $model = $model();
        }
        if (!is_null($model)) {
            if ($model instanceof $this->modelClass) {
                $this->model = $model;
            }
            else {
                $this->model = $this->firstByUnique($model);
            }
        }
        return $this->model;
    }

    /**
     * @throws DatabaseException
     */
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

    public function newModel(): Model
    {
        $modelClass = $this->modelClass;
        return $this->pinned(false) ? ($this->model = new $modelClass) : new $modelClass;
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

    /**
     * @throws DatabaseException
     */
    public function catch(Closure $callback): mixed
    {
        try {
            return $callback();
        }
        catch (Throwable $exception) {
            throw DatabaseException::from($exception);
        }
    }

    /**
     * @throws DatabaseException
     */
    public function createWithAttributes(array $attributes = []): Model
    {
        return $this->model = $this->catch(function () use ($attributes) {
            return $this->newQuery()->create($attributes);
        });
    }

    /**
     * @throws DatabaseException
     */
    public function firstOrCreateWithAttributes(array $attributes = [], array $values = []): Model
    {
        return $this->model = $this->catch(function () use ($attributes, $values) {
            return $this->newQuery()->firstOrCreate($attributes, $values);
        });
    }

    /**
     * @throws DatabaseException
     */
    public function updateWithAttributes(array $attributes = []): Model
    {
        $this->catch(function () use ($attributes) {
            return $this->model->update($attributes);
        });
        return $this->model;
    }

    /**
     * @throws DatabaseException
     */
    public function updateOrCreateWithAttributes(array $attributes, array $values = []): Model
    {
        return $this->model = $this->catch(function () use ($attributes, $values) {
            return $this->newQuery()->updateOrCreate($attributes, $values);
        });
    }

    /**
     * @throws DatabaseException
     */
    protected function executeDelete(Builder|Model $query): bool
    {
        $this->catch(function () use ($query) {
            return $this->force(false) && $this->modelUseSoftDeletes()
                ? $query->forceDelete()
                : $query->delete();
        });
        return true;
    }

    /**
     * @throws DatabaseException
     */
    public function delete(): bool
    {
        return $this->executeDelete($this->model);
    }

    /**
     * @throws DatabaseException
     */
    protected function executeAll(Builder $query): Collection
    {
        return $this->catch(function () use ($query) {
            return $query->get();
        });
    }

    /**
     * @throws DatabaseException
     */
    protected function executePagination(Builder $query, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->catch(function () use ($query, $perPage) {
            return $query->paginate($perPage);
        });
    }

    /**
     * @throws DatabaseException
     */
    protected function executeFirst(Builder $query): ?Model
    {
        $model = $this->catch(function () use ($query) {
            return $this->strict(true) ? $query->firstOrFail() : $query->first();
        });
        return $this->pinned(false) ? ($this->model = $model) : $model;
    }

    /**
     * @throws DatabaseException
     */
    protected function executeCount(Builder $query): int
    {
        return $this->catch(function () use ($query) {
            return $query->count();
        });
    }

    public function whereQuery(): Builder
    {
        $query = $this->query();
        foreach ($this->wheres([]) as $queryCondition) {
            $queryCondition($query);
        }
        return $query;
    }

    public function queryWhere(array $conditions = []): Builder
    {
        $query = $this->whereQuery();
        foreach ($conditions as $column => $value) {
            if ($value instanceof QueryCondition) {
                $value($query);
            }
            elseif (is_callable($value)) {
                $value($query, $value, $conditions);
            }
            elseif (is_int($column)) {
                if (is_array($value) && count($value) > 0) {
                    if (isset($value['column'])) {
                        $query->where(
                            $value['column'],
                            $value['operator'] ?? '=',
                            $value['value'] ?? null,
                            $value['boolean'] ?? 'and',
                        );
                    }
                    elseif (isset($value[0])) {
                        $query->where($value[0], $value[1] ?? null, $value[2] ?? null, $value[3] ?? 'and');
                    }
                    else {
                        $query->where($value);
                    }
                }
            }
            elseif (method_exists($this, $method = 'whereBy' . Str::studly($column))) {
                $this->{$method}($query, $value, $conditions);
            }
            else {
                $query->where($column, $value);
            }
        }
        return $query;
    }

    /**
     * @throws DatabaseException
     */
    public function all(array $conditions = []): Collection
    {
        return $this->executeAll($this->queryWhere($conditions));
    }

    /**
     * @throws DatabaseException
     */
    public function pagination(array $conditions = [], int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->executePagination($this->queryWhere($conditions), $perPage);
    }

    /**
     * @throws DatabaseException
     */
    public function firstPagination(array $conditions = [], int $perPage = self::PER_PAGE): Collection
    {
        $conditions[] = new LimitCondition($perPage);
        return $this->executeAll($this->queryWhere($conditions));
    }

    /**
     * @throws DatabaseException
     */
    public function first(array $conditions = []): ?Model
    {
        return $this->executeFirst($this->queryWhere($conditions));
    }

    /**
     * @throws DatabaseException
     */
    public function next(array $conditions = [], int $perPage = self::PER_PAGE, &$hasMore = false): Collection
    {
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

    /**
     * @throws DatabaseException
     */
    public function count(array $conditions = []): int
    {
        return $this->executeCount($this->queryWhere($conditions));
    }

    /**
     * @throws DatabaseException
     */
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

    /**
     * @throws DatabaseException
     */
    public function firstByKey(int|string $key): ?Model
    {
        return $this->first([$this->newModel()->getKeyName() => $key]);
    }

    /**
     * @throws DatabaseException
     */
    public function firstByUnique(int|string $unique): ?Model
    {
        $uniqueWhere = [];
        foreach ($this->newModel()->uniques as $key) {
            $uniqueWhere[$key] = $unique;
        }
        return $this->first([['column' => $uniqueWhere, 'boolean' => 'or']]);
    }

    /**
     * @throws DatabaseException
     */
    public function allByKeys(array $keys): Collection
    {
        return $this->all([$this->newModel()->getKeyName() => $keys]);
    }

    /**
     * @throws DatabaseException
     */
    public function deleteByKey(int|string $key)
    {
        return $this->executeDelete($this->queryByKey($key));
    }

    /**
     * @throws DatabaseException
     */
    public function deleteByKeys(array $keys)
    {
        return $this->executeDelete($this->queryByKeys($keys));
    }
}