<?php

namespace App\Support\Http\Resources;

use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

/**
 * @property IJsonResource $resource
 */
class ArrayPaginatedResourceResponse extends PaginatedResourceResponse implements ArrayResponsable
{
    protected function wrapper(): ?string
    {
        return $this->resource->getWrapper();
    }

    public function toArray($request): array
    {
        return $this->wrap(
            $this->resource->resolve($request),
            array_merge_recursive(
                $this->paginationInformation($request),
                $this->resource->with($request),
                $this->resource->additional
            )
        );
    }
}