<?php

namespace App\Support\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceResponse;

/**
 * @property IJsonResource $resource
 */
class ArrayResourceResponse extends ResourceResponse implements ArrayResponsable
{
    protected function wrapper(): ?string
    {
        return $this->resource->getWrapper();
    }

    public function toArray($request): array
    {
        return $this->wrap(
            $this->resource->resolve($request),
            $this->resource->with($request),
            $this->resource->additional
        );
    }
}