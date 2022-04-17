<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as FilesystemStorage;
use InvalidArgumentException;

abstract class DiskStorage extends Storage
{
    protected Filesystem|FilesystemAdapter $disk;

    protected string $rootPath;

    protected string $visibility = Filesystem::VISIBILITY_PRIVATE;

    protected string $dirSeparator;

    protected ?string $relativePath = null;

    public function __construct(?string $diskName)
    {
        if (is_null($diskName)) {
            throw new InvalidArgumentException('Disk must be provided.');
        }
        $this->disk = FilesystemStorage::disk($diskName);
    }

    public function getDisk(): Filesystem|FilesystemAdapter
    {
        return $this->disk;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    public function setRelativePath(string $relativePath, bool $check = true): static
    {
        $relativePath = str_replace(['\\', '/'], $this->dirSeparator, $relativePath);
        $this->relativePath = $check
            ? ($this->disk->fileExists($relativePath) ? $relativePath : null)
            : $relativePath;
        return $this;
    }

    public function has(): bool
    {
        return !is_null($this->relativePath);
    }

    public function storeFile(object|string $file, ?string $in = null): static
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
        return $this->setRelativePath(
            $this->disk->putFileAs(
                $in ?: join_paths(true, date('Y'), date('m'), date('d'), date('H')),
                $file,
                $file->hashName(),
                ['visibility' => $this->visibility]
            ),
            false
        );
    }
}