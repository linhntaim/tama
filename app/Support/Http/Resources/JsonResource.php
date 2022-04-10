<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;
use JsonSerializable;

class JsonResource extends BaseJsonResource implements IJsonResource
{
    use JsonResourceTrait, ResourceTransformTrait;

    public bool $preserveKeys = false;

    public static function collection($resource)
    {
        return tap(new AnonymousResourceCollection($resource, static::class), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
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