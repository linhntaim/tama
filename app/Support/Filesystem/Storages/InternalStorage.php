<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use BadMethodCallException;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

class InternalStorage extends Storage implements IHasInternalStorage
{
    public const NAME = 'internal';

    protected File|UploadedFile|null $file = null;

    public function getContent(): string
    {
        return $this->file->getContent();
    }

    public function getContentAsStream()
    {
        return $this->file->openFile();
    }

    public function has(): bool
    {
        return !is_null($this->file);
    }

    public function from(string|UploadedFile|File|Storage $file, ?string $in = null): static
    {
        throw new BadMethodCallException('Internal Storage does not support `from` method.');
    }

    public function setFile(string|SplFileInfo $file): static
    {
        $file = File::from($file);
        if ($file instanceof UploadedFile) {
            $this
                ->setName($file->getClientOriginalName())
                ->setMimeType($file->getClientMimeType())
                ->setSize($file->getSize());
        }
        elseif ($file instanceof File) {
            $this
                ->setName($file->getBasename())
                ->setMimeType($file->getMimeType())
                ->setSize($file->getSize());
        }
        $this->file = $file;
        return $this;
    }

    public function getFile(): File|UploadedFile
    {
        return $this->file;
    }

    public function getRealPath(): string
    {
        return $this->file->getRealPath();
    }
}