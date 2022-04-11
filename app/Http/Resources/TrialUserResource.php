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
                'daddy' => $this->modelResourceTransform(
                    User::factory()->make(),
                    UserResource::class,
                    $request
                ),
                'mummy' => $this->modelResourceTransform(
                    User::factory()->make(),
                    UserResource::class,
                    $request
                ),
                'children' => $this->modelResourceTransform(
                    User::factory()->count(2)->make(),
                    UserResource::class,
                    $request
                ),
            ]),
        ];
    }
}
