<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Resources\Concerns\ResourceResponseWrapper;
use App\Support\Http\Resources\Contracts\ArrayResponsible;
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
