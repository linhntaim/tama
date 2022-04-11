<?php

namespace App\Support\Http\Resources;

trait WrappedResourceTrait
{
    protected ?string $wrapped = null;

    public function setWrapped(?string $wrapped): static
    {
        $this->wrapped = $wrapped;
        return $this;
    }

    public function getWrapped(): ?string
    {
        return $this->wrapped;
    }
}