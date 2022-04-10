<?php

namespace App\Support\Http\Resources;

interface ArrayResponsable
{
    public function toArray($request): array;
}