<?php

namespace App\Support\Models;

interface IProtected
{
    public function getProtectedKey(): string;

    public function getProtectedValues(): array;

    public function isProtected(): bool;
}
