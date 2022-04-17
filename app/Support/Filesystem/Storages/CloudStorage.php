<?php

namespace App\Support\Filesystem\Storages;

class CloudStorage extends DiskStorage implements IHasPublicStorage, IHasExternalStorage
{
    use HasPublicStorage;

    public const NAME = 'cloud';

    public function __construct(?string $diskName = null)
    {
        parent::__construct($diskName ?: config('filesystems.cloud'));

        $this->rootPath = '';
        $this->dirSeparator = '/';
    }
}