<?php

namespace App\Support\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

class ResourceCollection extends BaseResourceCollection implements IWrappedResource
{
    use WrappedResourceTrait, ModelResourceTransformer;

    public function setResource(mixed $resource): static
    {
        $this->resource = $this->collectResource($resource);
        return $this;
    }

    public function toResponse($request): JsonResponse
    {
        if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
            return $this->preparePaginatedResponse($request);
        }

        return (new ResourceResponse($this))->toResponse($request);
    }

    protected function preparePaginatedResponse($request): JsonResponse
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        }
        elseif (!is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new PaginatedResourceResponse($this))->toResponse($request);
    }
}