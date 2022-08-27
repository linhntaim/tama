<?php

namespace App\Support\Models\Contracts;

interface HasProtected
{
    public function getProtectedKey(): string;

    public function getProtectedValues(): array;

    public function isProtected(): bool;
}
