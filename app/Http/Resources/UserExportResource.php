<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class UserExportResource extends UserResource
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