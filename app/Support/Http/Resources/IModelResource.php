<?php

namespace App\Support\Http\Resources;

interface IModelResource
{
    public function toArrayResponse($request): array;
}