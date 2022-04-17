<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Http\UploadedFile;

class InternalStorage extends Storage implements IHasInternalStorage
{
    public const NAME = 'internal';

    protected File|UploadedFile|null $file = null;

    public function setFile(object|string $file): static
    {
        $file = File::from($file);
        if ($file instanceof UploadedFile) {
            $this->name = $file->getClientOriginalName();
            $this->mimeType = $file->getClientMimeType();
            $this->size = $file->getSize();
        }
        elseif ($file instanceof File) {
            $this->name = $file->getBasename();
            $this->mimeType = $file->getMimeType();
            $this->size = $file->getSize();
        }
        $this->file = $file;
        return $this;
    }

    public function has(): bool
    {
        return !is_null($this->file);
    }
}