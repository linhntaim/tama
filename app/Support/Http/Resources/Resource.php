<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use JsonSerializable;

class Resource extends BaseResource implements IWrappedResource
{
    use ResourceWrapper, ModelResourceTransformer;

    public function setResource(mixed $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return parent::toArray($request);
    }

    public function toResponse($request): JsonResponse
    {
        return (new ResourceResponse($this))->toResponse($request);
    }
}