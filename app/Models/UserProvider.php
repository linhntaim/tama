<?php

namespace App\Models;

use App\Support\ModelProviders\ModelProvider;

/**
 * @method User newModel()
 * @property User $model
 */
class UserProvider extends ModelProvider
{
    public function modelClass(): string
    {
        return User::class;
    }
}