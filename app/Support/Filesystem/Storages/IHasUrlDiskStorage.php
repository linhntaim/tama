<?php

namespace App\Support\Filesystem\Storages;

interface IHasUrlDiskStorage extends IHasUrlStorage
{
    public function getRootUrl(): string;
}