<?php

namespace App\Support\Http\Resources;

interface ArrayResponsible
{
    public function toArray($request): array;
}