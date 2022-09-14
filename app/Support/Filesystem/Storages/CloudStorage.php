<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Filesystem\Storages\Concerns\HasUrlDiskStorage;
use App\Support\Filesystem\Storages\Contracts\EditableStorage as EditableStorageContract;
use App\Support\Filesystem\Storages\Contracts\HasExternalStorage as HasExternalStorageContract;
use App\Support\Filesystem\Storages\Contracts\HasUrlDiskStorage as HasUrlDiskStorageContract;
use App\Support\Filesystem\Storages\Contracts\PublicPublishableStorage as PublicPublishableStorageContract;
use Illuminate\Support\Str;
use SplFileInfo;

class CloudStorage extends DiskStorage implements HasUrlDiskStorageContract,
                                                  HasExternalStorageContract,
                                                  PublicPublishableStorageContract,
                                                  EditableStorageContract
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
