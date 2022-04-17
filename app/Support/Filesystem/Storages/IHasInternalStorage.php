<?php

namespace App\Support\Filesystem\Storages;

interface IHasInternalStorage
{
    public function setFile(object|string $file): static;
}