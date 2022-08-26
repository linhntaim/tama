<?php

namespace App\Support\Http\Resources\Contracts;

interface ArrayResponsibleResource
{
    public function toArrayResponse($request): array;
}
