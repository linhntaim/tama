<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Support\Str;

class LocalStorage extends DiskStorage implements IHasInternalStorage
{
    public const NAME = 'local';

    public function __construct(?string $diskName = null)
    {
        parent::__construct($diskName ?: config('filesystems.default'));

        $this->dirSeparator = DIRECTORY_SEPARATOR;
        $this->rootPath = join_paths(false, $this->disk->path(''));
    }

    public function setFile(object|string $file): static
    {
        if (($file = File::from($file)) instanceof File
            && Str::startsWith(
                $path = join_paths(false, $file->getRealPath()),
                $rootPath = $this->rootPath . DIRECTORY_SEPARATOR
            )) {
            $this->name = $file->getBasename();
            $this->mimeType = $file->getMimeType();
            $this->size = $file->getSize();
            return $this->setRelativePath(str_replace($rootPath, '', $path), false);
        }
        return $this;
    }
}