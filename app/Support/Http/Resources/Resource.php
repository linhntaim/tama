<?php

namespace App\Support\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource as BaseResource;
use JsonSerializable;

class Resource extends BaseResource implements IWrappedResource, IArrayResponsibleResource
{
    use ResourceWrapper, ResourceTransformer;

    public static string $collectionClass = ResourceCollection::class;

    public static function collection($resource)
    {
        $resourceCollectionClass = static::$collectionClass;
        return tap(
            new $resourceCollectionClass($resource, static::class),
            function ($collection) {
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
