<?php

namespace App\Support\Http\Resources;

class ModelResource extends Resource implements IModelResource
{
    protected ?string $wrapped = 'model';

    public static function collection($resource): ModelResourceCollection
    {
        return tap(
            new ModelResourceCollection($resource, static::class),
            function (ModelResourceCollection $collection) {
                $collection->collects = static::class;
                if (property_exists(static::class, 'preserveKeys')) {
                    $collection->preserveKeys = (new static([]))->preserveKeys === true;
                }
            }
        );
    }

    public bool $preserveKeys = false;

    public function toArrayResponse($request): array
    {
        return (new ResourceResponse($this))->toArray($request);
    }
}