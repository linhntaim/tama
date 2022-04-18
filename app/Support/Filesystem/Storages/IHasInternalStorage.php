<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Http\UploadedFile;
use SplFileInfo;

interface IHasInternalStorage
{
    public function setFile(string|SplFileInfo $file): static;

    public function getFile(): File|UploadedFile;

    public function getRealPath(): string;
}