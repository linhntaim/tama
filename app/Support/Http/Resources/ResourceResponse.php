<?php

namespace App\Support\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceResponse as BaseResourceResponse;

class ResourceResponse extends BaseResourceResponse implements ArrayResponsible
{
    use ResourceResponseWrapper;

    public function toArray($request): array
    {
        return $this->wrap(
            $this->resource->resolve($request),
            $this->resource->with($request),
            $this->resource->additional
        );
    }
}
