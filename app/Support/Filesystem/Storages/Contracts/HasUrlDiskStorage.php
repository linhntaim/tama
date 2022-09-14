<?php

namespace App\Support\Filesystem\Storages\Contracts;

interface HasUrlDiskStorage extends HasUrlStorage
{
    public function getRootUrl(): string;
}
