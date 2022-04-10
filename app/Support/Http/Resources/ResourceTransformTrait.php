<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use App\Support\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

trait ResourceTransformTrait
{
    protected function resourceTransform(
        Model|Collection|LengthAwarePaginator|null $model,
        string|callable                            $resourceClass = JsonResource::class,
        ?Request                                   $request = null,
        ?string                                    $wrapper = null,
    ): ?array
    {
        return is_null($model)
            ? null
            : (fn(): IJsonResource => is_callable($resourceClass)
                ? $resourceClass($model)
                : ($model instanceof Model
                    ? (is_subclass_of($resourceClass, JsonResource::class)
                        ? new $resourceClass($model)
                        : new JsonResource($model))
                    : (is_subclass_of($resourceClass, JsonResourceCollection::class)
                        ? new $resourceClass($model)
                        : (is_subclass_of($resourceClass, JsonResource::class)
                            ? $resourceClass::collection($model)
                            : new JsonResourceCollection($model)))))()
                ->setWrapper($wrapper)
                ->toArrayResponse($request ?? request());
    }
}