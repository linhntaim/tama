<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Support\Http\Resources\Concerns\ResourceWrapper;
use App\Support\Http\Resources\Contracts\ArrayResponsibleResource;
use App\Support\Http\Resources\Contracts\WrappedResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use JsonSerializable;

class Resource extends BaseResource implements WrappedResource, ArrayResponsibleResource
{
    use ResourceWrapper, ResourceTransformer;

    public static string $collectionClass = ResourceCollection::class;

    public static function collection($resource)
    {
        $resourceCollectionClass = static::$collectionClass;
        return tap(
            new $resourceCollectionClass($resource, static::class),
            static function ($collection) {
                $collection->collects = static::class;
                if (property_exists(static::class, 'preserveKeys')) {
                    $collection->preserveKeys = (new static([]))->preserveKeys === true;
                }
            }
        );
    }

    public bool $preserveKeys = false;

    public function setResource(mixed $resource): static
    {
        $this->resource = $resource;
        return $this;
    }

    public function resolve($request = null): array
    {
        if (is_null($this->resource)) {
            return [];
        }
        return parent::resolve($request);
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

    public function toArrayResponse($request): array
    {
        return (new ResourceResponse($this))->toArray($request);
    }
}
