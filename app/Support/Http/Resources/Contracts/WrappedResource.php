<?php

namespace App\Support\Http\Resources\Contracts;

interface WrappedResource
{
    public function setWrapped(?string $wrapped): static;

    public function getWrapped(): ?string;
}
