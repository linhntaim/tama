<?php

namespace App\Support\Models;

use App\Support\Models\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as BaseModel;
use RuntimeException;

/**
 * @method MorphToMany morphToMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $inverse = false)
 * @method MorphToMany morphedByMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
 */
abstract class Model extends BaseModel
{
    protected $perPage = 10;

    public array $uniques = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        array_unshift($this->uniques, $this->getKeyName());
    }

    protected function newMorphToMany(Builder $query, BaseModel $parent, $name, $table, $foreignPivotKey,
                                              $relatedPivotKey, $parentKey, $relatedKey,
                                              $relationName = null, $inverse = false): MorphToMany
    {
        return new MorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse);
    }

    public static function alias(Model $model): static
    {
        if (!is_a(static::class, $model::class, true)
            || ($newModel = new static())->getTable() !== $model->getTable()) {
            throw new RuntimeException('Current class must be the same model class or has the model class as one of its parents.');
        }

        $newModel->attributes = $model->attributes;
        $newModel->original = $model->original;
        $newModel->relations = $model->relations;
        return $newModel;
    }
}
