<?php

namespace App\Support\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection as BaseResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use JsonSerializable;

class ResourceCollection extends BaseResourceCollection implements IWrappedResource, IArrayResponsibleResource
{
    use ResourceWrapper, ResourceTransformer;

    public bool $preserveKeys = false;

    public function __construct($resource, ?string $collects = null)
    {
        if (!is_null($collects)) {
            $this->collects = $collects;
        }
        if (is_null($resource)) {
            $resource = [];
        }

        parent::__construct($resource);
    }

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

    public function toArrayResponse($request): array
    {
        if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
            return $this->prepareArrayPaginatedResponse($request);
        }

        return (new ResourceResponse($this))->toArray($request);
    }

    protected function prepareArrayPaginatedResponse($request): array
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        }
        elseif (!is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new PaginatedResourceResponse($this))->toArray($request);
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
