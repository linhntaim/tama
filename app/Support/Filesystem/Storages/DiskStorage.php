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

    protected string $dirSeparator;

    protected ?string $relativePath = null;

    public function __construct(?string $diskName)
    {
        if (is_null($diskName)) {
            throw new InvalidArgumentException('Disk must be not null.');
        }
        $this->disk = FilesystemStorage::disk($diskName);
    }

    public function getContent(): string
    {
        return $this->disk->get($this->relativePath);
    }

    public function getContentAsStream()
    {
        return $this->disk->readStream($this->relativePath);
    }

    public function has(): bool
    {
        return !is_null($this->relativePath);
    }

    public function from(string|File|UploadedFile|Storage $file, ?string $in = null): static
    {
        if ($file instanceof Storage) {
            $filename = compose_filename(null, $file->getExtension());
            $resource = $file instanceof InlineStorage
                ? $file->getContent() : $file->getContentAsStream();
            $this->setName($file->getName())
                ->setMimeType($file->getMimeType())
                ->setSize($file->getSize());
        }
        else {
            $file = File::from($file);
            $filename = $file->hashName();
            $resource = $file->openFile();
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
        }

        $this->disk->put(
            $relativePath = ($in ?: $this->timelyPath()) . $this->dirSeparator . $filename,
            $resource,
            ['visibility' => $this->visibility]
        );
        return $this->setRelativePath($relativePath, false);
    }

    protected function timelyPath(): string
    {
        return join_paths(true, date('Y'), date('m'), date('d'), date('H'));
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

    public function getRelativePath(): ?string
    {
        return $this->relativePath;
    }
}