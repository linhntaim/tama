<?php

namespace App\Http\Resources;

use App\Models\User;

/**
 * @mixin User
 */
class TrialUserResource extends UserResource
{
    public function toArray($request): array
    {
        return [
            $this->merge(parent::toArray($request)),
            $this->merge([
                'transformed_user' => $this->resourceTransform(
                    User::factory()->make(),
                    UserResource::class,
                    $request
                ),
            ]),
        ];
    }
}
