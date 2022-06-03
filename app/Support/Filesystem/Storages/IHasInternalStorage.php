<?php

namespace App\Support\Filesystem\Storages;

interface IHasInternalStorage
{
    public function getRealPath(): string;
}
