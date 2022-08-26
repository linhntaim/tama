<?php

namespace App\Http\Resources;

use App\Models\Holding;
use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Support\Http\Resources\ModelResource;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @mixin Holding
 */
class CurrentHoldingResource extends ModelResource
{
    use ResourceTransformer;

    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return parent::toArray($request)
            + [
                'assets' => $this->resourceTransform($this->orderedAssets),
            ];
    }
}
