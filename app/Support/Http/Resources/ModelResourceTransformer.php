<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use App\Support\Models\Model;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;

trait ModelResourceTransformer
{
    protected function modelResourceFrom($resource, string $modelResourceClass = ModelResource::class): ?IModelResource
    {
        if ($resource instanceof Model) {
            if (!is_a($modelResourceClass, ModelResource::class, true)) {
                $modelResourceClass = ModelResource::class;
            }
            return new $modelResourceClass($resource);
        }

        if ($resource instanceof Collection
            || $resource instanceof AbstractPaginator
            || $resource instanceof AbstractCursorPaginator) {
            if (is_a($modelResourceClass, ModelResource::class, true)) {
                return $modelResourceClass::collection($resource);
            }
            if (!is_a($modelResourceClass, ModelResourceCollection::class, true)) {
                $modelResourceClass = ModelResourceCollection::class;
            }
            return new $modelResourceClass($resource);
        }

        if (is_null($resource)
            && is_string($modelResourceClass)
            && is_subclass_of($modelResourceClass, IModelResource::class, true)) {
            return new $modelResourceClass(null);
        }

        return null;
    }

    protected function modelResourceTransform($resource, string $modelResourceClass = ModelResource::class, ?Request $request = null, ?string $wrap = null): ?array
    {
        return modify(
            $this->modelResourceFrom($resource, $modelResourceClass),
            function (IModelResource|IWrappedResource|null $resource) use ($request, $wrap) {
                return is_null($resource) ? null : $resource->setWrapped($wrap)->toArrayResponse($request);
            }
        );
    }
}
