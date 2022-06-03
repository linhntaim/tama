<?php

namespace App\Support\Http\Resources;

interface IArrayResponsibleResource
{
    public function toArrayResponse($request): array;
}
