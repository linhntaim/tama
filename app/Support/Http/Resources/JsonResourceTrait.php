<?php

namespace App\Support\Http\Resources;

trait JsonResourceTrait
{
    public ?string $wrapper = null;

    public function setWrapper(?string $wrapper): static
    {
        $this->wrapper = $wrapper;
        return $this;
    }

    public function getWrapper(): ?string
    {
        return $this->wrapper;
    }

    public function toArrayResponse($request): array
    {
        return (new ArrayResourceResponse($this))->toArray($request);
    }
}