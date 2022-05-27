<?php

namespace App\Support\Http\Resources;

class ModelResourceCollection extends ResourceCollection implements IModelResource
{
    protected ?string $wrapped = 'models';
}
