<?php

namespace App\Support\Http\Resources\Concerns;

use App\Support\Http\Resources\Contracts\ArrayResponsibleResource;
use App\Support\Http\Resources\Contracts\WrappedResource;
use App\Support\Http\Resources\ModelResource;
use App\Support\Http\Resources\ModelResourceCollection;
use App\Support\Http\Resources\Resource;
use App\Support\Http\Resources\ResourceCollection;
use App\Support\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

trait ResourceTransformer
{
    protected function resourceFrom($resource, string $resourceClass = Resource::class): ArrayResponsibleResource
    {
        if (is_array($resource)) {
            $item = $resource[0] ?? null;
            $isCollection = true;
        }
        elseif ($resource instanceof Collection
            || $resource instanceof AbstractPaginator
            || $resource instanceof AbstractCursorPaginator) {
            $item = $resource->first();
            $isCollection = true;
        }
        else {
            $item = $resource;
            $isCollection = false;
        }

        if ($isCollection) {
            if ($item instanceof Model) {
                if (is_a($resourceClass, ModelResource::class, true)) {
                    return $resourceClass::collection($resource);
                }
                if (!is_a($resourceClass, ModelResourceCollection::class, true)) {
                    return new ModelResourceCollection($resource);
                }
            }
            else {
                if (is_a($resourceClass, Resource::class, true)) {
                    return $resourceClass::collection($resource);
                }
                if (!is_a($resourceClass, ResourceCollection::class, true)) {
                    return new ResourceCollection($resource);
                }
            }
        }
        elseif ($resource instanceof Model) {
            if (!is_a($resourceClass, ModelResource::class, true)) {
                return new ModelResource($resource);
            }
        }
        elseif (!is_a($resourceClass, Resource::class, true)) {
            return new Resource($resource);
        }

        return new $resourceClass($resource);
    }

    protected function resourceTransform($resource, string $resourceClass = Resource::class, ?Request $request = null, ?string $wrap = null): ?array
    {
        return modify(
            $this->resourceFrom($resource, $resourceClass),
            static function (ArrayResponsibleResource|WrappedResource|null $resource) use ($request, $wrap) {
                return is_null($resource) ? null : $resource->setWrapped($wrap)->toArrayResponse($request);
            }
        );
    }
}
