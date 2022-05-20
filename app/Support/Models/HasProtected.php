<?php

namespace App\Support\Models;

trait HasProtected
{
    public function getProtectedKey(): string
    {
        return $this->primaryKey;
    }

    public function getProtectedValues(): array
    {
        return [];
    }

    public function isProtected(): bool
    {
        return in_array($this->attributes[$this->getProtectedKey()], $this->getProtectedValues());
    }
}
