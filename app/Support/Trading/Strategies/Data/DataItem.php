<?php

namespace App\Support\Trading\Strategies\Data;

abstract class DataItem
{
    protected string $createdAt;

    public function setCreatedAt(string $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public abstract function getValue(): float;
}
