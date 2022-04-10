<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use JsonSerializable;

class JsonResourceCollection extends ResourceCollection implements IJsonResource
{
    use JsonResourceTrait, ResourceTransformTrait;

    public function toArrayResponse($request): array
    {
        if ($this->resource instanceof AbstractPaginator || $this->resource instanceof AbstractCursorPaginator) {
            return $this->prepareArrayPaginatedResponse($request);
        }
        return (new ArrayResourceResponse($this))->toArray($request);
    }

    protected function prepareArrayPaginatedResponse($request): array
    {
        if ($this->preserveAllQueryParameters) {
            $this->resource->appends($request->query());
        }
        elseif (!is_null($this->queryParameters)) {
            $this->resource->appends($this->queryParameters);
        }

        return (new ArrayPaginatedResourceResponse($this))->toArray($request);
    }

    /**
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return parent::toArray($request);
    }
}