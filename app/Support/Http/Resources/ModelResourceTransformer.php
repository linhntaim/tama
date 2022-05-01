<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use App\Support\Models\Model;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

trait ModelResourceTransformer
{
    protected function modelResourceFrom($resource, $modelResourceClass = ModelResource::class): ?IModelResource
    {
        if ($resource instanceof Model) {
            if (!is_subclass_of($modelResourceClass, ModelResource::class)) {
                $modelResourceClass = ModelResource::class;
            }
            return new $modelResourceClass($resource);
        }

        if ($resource instanceof Collection
            || $resource instanceof AbstractPaginator
            || $resource instanceof AbstractCursorPaginator) {
            if (is_subclass_of($modelResourceClass, ModelResource::class)) {
                return $modelResourceClass::collection($resource);
            }
            if (!is_subclass_of($modelResourceClass, ModelResourceCollection::class)) {
                $modelResourceClass = ModelResourceCollection::class;
            }
            return new $modelResourceClass($resource);
        }

        if (is_null($resource)
            && is_string($modelResourceClass)
            && is_subclass_of($modelResourceClass, IModelResource::class)) {
            return new $modelResourceClass(null);
        }

        return null;
    }

    protected function modelResourceTransform($resource, $modelResourceClass, ?Request $request = null, ?string $wrap = null): ?array
    {
        return modify(
            $this->modelResourceFrom($resource, $modelResourceClass),
            function (IModelResource|IWrappedResource|null $resource) use ($request, $wrap) {
                return is_null($resource) ? null : $resource->setWrapped($wrap)->toArrayResponse($request);
            }
        );
    }
}