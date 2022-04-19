<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Support\Str;
use SplFileInfo;

class CloudStorage extends DiskStorage implements IHasUrlDiskStorage, IHasExternalStorage, IPublicPublishableStorage
{
    use HasUrlDiskStorage;

    public const NAME = 'cloud';

    public function __construct(?string $diskName = null)
    {
        parent::__construct($diskName ?: config('filesystems.cloud'));

        $this->rootPath = '';
        $this->dirSeparator = '/';
    }

    public function setFile(SplFileInfo|string $file): static
    {
        if (is_string($file)) {
            if (Str::startsWith($file, $rootUrl = $this->getRootUrl() . '/')) {
                $file = Str::after($file, $rootUrl);
            }
            $this->setRelativeFile($file);
        }
        return $this;
    }
}