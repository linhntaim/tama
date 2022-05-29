<?php

namespace App\Support\Http\Resources;

class ModelResource extends Resource implements IModelResource
{
    protected ?string $wrapped = 'model';

    public static string $collectionClass = ModelResourceCollection::class;
}
