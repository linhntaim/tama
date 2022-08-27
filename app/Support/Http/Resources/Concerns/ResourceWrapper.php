<?php

namespace App\Support\Http\Resources\Concerns;

trait ResourceWrapper
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
