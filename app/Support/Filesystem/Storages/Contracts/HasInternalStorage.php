<?php

namespace App\Support\Filesystem\Storages\Contracts;

interface HasInternalStorage
{
    public function getRealPath(): string;
}
