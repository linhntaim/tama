<?php

namespace App\Support\Http\Resources\Contracts;

interface ArrayResponsible
{
    public function toArray($request): array;
}
