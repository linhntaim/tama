<?php

namespace App\Support\Http\Resources;

use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;

class ModelResourceCollection extends ResourceCollection implements IModelResource
{
    protected ?string $wrapped = 'models';

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
}