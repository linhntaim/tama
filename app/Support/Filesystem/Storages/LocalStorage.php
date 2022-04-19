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
        $this->rootPath = $this->dirPath($this->disk->path(''), false);
    }

    public function setFile(string|SplFileInfo $file): static
    {
        if (($f = File::from($file, false)) instanceof File
            && $f->isFile()
            && Str::startsWith(
                $path = $this->dirPath($f->getRealPath(), false),
                $rootPath = $this->rootPath . $this->dirSeparator
            )) {
            return parent::setFile(Str::after($path, $rootPath))
                ->setName($f->getBasename())
                ->setMimeType($f->getMimeType())
                ->setExtension($f->getExtension())
                ->setSize($f->getSize());
        }
        if (is_string($file)) {
            return $this->setRelativeFile($file);
        }
        return $this;
    }

    public function getFile(): File
    {
        return new File($this->getRealPath());
    }

    public function getRealPath(): string
    {
        return $this->rootPath . $this->dirSeparator . $this->file;
    }
}