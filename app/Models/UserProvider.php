<?php

namespace App\Models;

use App\Support\ModelProviders\ModelProvider;

/**
 * @property User|null $model
 */
class UserProvider extends ModelProvider
{
    public function modelClass(): string
    {
        return User::class;
    }
}