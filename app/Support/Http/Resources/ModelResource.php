<?php

namespace App\Support\Http\Resources;

use App\Support\Http\Resources\Contracts\ModelResource as ModelResourceContract;

class ModelResource extends Resource implements ModelResourceContract
{
    protected ?string $wrapped = 'model';

    public static string $collectionClass = ModelResourceCollection::class;
}
