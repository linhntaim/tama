<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use JsonSerializable;

class ResourceCollection extends BaseResourceCollection implements IWrappedResource
{
    use ResourceWrapper, ModelResourceTransformer;

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

    /**
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return parent::toArray($request);
    }
}