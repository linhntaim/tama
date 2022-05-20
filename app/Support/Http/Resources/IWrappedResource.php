<?php

namespace App\Support\Http\Resources;

interface IWrappedResource
{
    public function setWrapped(?string $wrapped): static;

    public function getWrapped(): ?string;
}
