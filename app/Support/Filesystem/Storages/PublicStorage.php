<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Filesystem\Storages\Concerns\HasUrlDiskStorage;
use App\Support\Filesystem\Storages\Contracts\HasUrlDiskStorage as HasUrlDiskStorageContract;
use App\Support\Filesystem\Storages\Contracts\PublicPublishableStorage as PublicPublishableStorageContract;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use SplFileInfo;

class PublicStorage extends LocalStorage implements HasUrlDiskStorageContract,
                                                    PublicPublishableStorageContract
{
    use HasUrlDiskStorage;

    public const NAME = 'public';

    public function __construct()
    {
        parent::__construct('public');

        $this->setVisibility(Filesystem::VISIBILITY_PUBLIC);
    }

    public function setFile(string|SplFileInfo $file): static
    {
        if (is_string($file) && Str::startsWith($file, $rootUrl = $this->getRootUrl() . '/')) {
            return $this->setRelativeFile(Str::after($file, $rootUrl));
        }
        return parent::setFile($file);
    }
}
