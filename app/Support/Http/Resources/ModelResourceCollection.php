<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Resources\Contracts\ModelResource as ModelResourceContract;

class ModelResourceCollection extends ResourceCollection implements ModelResourceContract
{
    protected ?string $wrapped = 'models';
}
