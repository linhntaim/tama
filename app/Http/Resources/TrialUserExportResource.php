<?php

namespace App\Http\Resources;

use App\Support\Http\Resources\ModelResource;
use App\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * @mixin User
 */
class TrialUserExportResource extends ModelResource
{
    public function toArray($request): array|JsonSerializable|Arrayable
    {
        return [
            $this->id,
            $this->name,
            $this->email,
        ];
    }
}