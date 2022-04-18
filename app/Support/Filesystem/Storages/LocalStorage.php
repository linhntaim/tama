<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Support\Str;
use SplFileInfo;

class LocalStorage extends DiskStorage implements IHasInternalStorage
{
    public const NAME = 'local';

    public function __construct(?string $diskName = null)
    {
        parent::__construct($diskName ?: config('filesystems.default'));

        $this->dirSeparator = DIRECTORY_SEPARATOR;
        $this->rootPath = join_paths(false, $this->disk->path(''));
    }

    public function setFile(string|SplFileInfo $file): static
    {
        if (($file = File::from($file)) instanceof File
            && Str::startsWith(
                $path = join_paths(false, $file->getRealPath()),
                $rootPath = $this->rootPath . DIRECTORY_SEPARATOR
            )) {
            return $this
                ->setRelativePath(str_replace($rootPath, '', $path), false)
                ->setName($file->getBasename())
                ->setMimeType($file->getMimeType())
                ->setSize($file->getSize());
        }
        return $this;
    }

    public function getFile(): File
    {
        return new File($this->getRealPath());
    }

    public function getRealPath(): string
    {
        return $this->rootPath . $this->dirSeparator . $this->relativePath;
    }
}