<?php

namespace App\Support\Http\Resources;

interface IJsonResource
{
    public function setWrapper(?string $wrapper): static;

    public function getWrapper(): ?string;

    public function toArrayResponse($request): array;
}