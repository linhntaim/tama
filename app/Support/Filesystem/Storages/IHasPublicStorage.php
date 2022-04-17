<?php

namespace App\Support\Filesystem\Storages;

interface IHasPublicStorage extends IHasUrlStorage
{
    public function getRootUrl(): string;
}