<?php

namespace App\Trading\Http\Resources;

use App\Support\Http\Resources\Concerns\ResourceTransformer;
use App\Support\Http\Resources\ModelResource;
use App\Trading\Models\Holding;
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
