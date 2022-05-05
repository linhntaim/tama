<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Exceptions\FileNotFoundException;
use App\Support\Http\File;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as FilesystemStorage;
use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;

abstract class DiskStorage extends Storage
{
    protected Filesystem|FilesystemAdapter $disk;

    protected string $rootPath;

    protected string $dirSeparator;

    public function __construct(?string $diskName)
    {
        if (is_null($diskName)) {
            throw new InvalidArgumentException('Disk must be not null.');
        }
        $this->disk = FilesystemStorage::disk($diskName);
    }

    /**
     * @throws FileNotFoundException
     */
    public function fromFile(string|SplFileInfo|Storage $file, ?string $in = null): static
    {
        if ($file instanceof Storage) {
            $filename = compose_filename(null, $file->getExtension());
            $resource = $file instanceof InlineStorage
                ? $file->getContent() : $file->getStream();
            $this->setName($file->getName())
                ->setMimeType($file->getMimeType())
                ->setExtension($file->getExtension())
                ->setSize($file->getSize());
        }
        else {
            $file = File::from($file);
            $filename = $file->hashName();
            if (($resource = fopen($path = $file->getRealPath(), 'r')) === false) {
                throw new RuntimeException(sprintf('Cannot get stream from the file [%s].', $path));
            }
            if ($file instanceof UploadedFile) {
                $this
                    ->setName($file->getClientOriginalName())
                    ->setMimeType($file->getClientMimeType())
                    ->setExtension($file->getClientOriginalExtension())
                    ->setSize($file->getSize());
            }
            elseif ($file instanceof File) {
                $this
                    ->setName($file->getBasename())
                    ->setMimeType($file->getMimeType())
                    ->setExtension($file->getExtension())
                    ->setSize($file->getSize());
            }
        }

        // prevent filename from existing
        $in = $in ? $this->dirPath($in) : $this->defaultPath();
        while ($this->disk->fileExists($file = $in . $this->dirSeparator . $filename)) {
            $filename = compose_filename(null, pathinfo($filename, PATHINFO_EXTENSION));
        }

        $this->disk->put(
            $file,
            $resource,
            ['visibility' => $this->getVisibility()]
        );
        return parent::setFile($file);
    }

    public function setRelativeFile(string $file): static
    {
        if ($this->disk->exists($file = $this->dirPath($file))) {
            return parent::setFile($file)
                ->setName(basename($file))
                ->setMimeType($this->disk->mimeType($file))
                ->setExtension(pathinfo($file, PATHINFO_EXTENSION))
                ->setSize($this->disk->size($file));
        }
        return $this;
    }

    public function getSize(): int
    {
        return $this->disk->size($this->file);
    }

    public function getContent(): string
    {
        return $this->disk->get($this->file);
    }

    public function getStream()
    {
        return $this->disk->readStream($this->file);
    }

    public function delete(): static
    {
        $this->disk->delete($this->file);
        return $this;
    }

    public function getDisk(): Filesystem|FilesystemAdapter
    {
        return $this->disk;
    }

    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    protected function dirPath(string $path, bool $relative = true): string
    {
        $path = str_replace(['\\', '/'], $this->dirSeparator, $path);
        return $relative ? trim_more($path, $this->dirSeparator) : rtrim_more($path, $this->dirSeparator);
    }

    protected function defaultPath(): string
    {
        return implode($this->dirSeparator, [date('Y'), date('m'), date('d'), date('H')]);
    }
}
