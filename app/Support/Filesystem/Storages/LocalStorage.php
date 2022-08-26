<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Exceptions\FileException;
use App\Support\Exceptions\FileNotFoundException;
use App\Support\Filesystem\Filers\Filer;
use App\Support\Filesystem\Storages\Contracts\DirectEditableStorage as DirectEditableStorageContract;
use App\Support\Http\File;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileInfo;

class LocalStorage extends DiskStorage implements DirectEditableStorageContract
{
    public const NAME = 'local';

    public function __construct(?string $diskName = null)
    {
        parent::__construct($diskName ?: config('filesystems.default'));

        $this->dirSeparator = DIRECTORY_SEPARATOR;
        $this->rootPath = $this->dirPath($this->disk->path(''), false);
    }

    /**
     * @throws FileNotFoundException
     */
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

    public function getRealPath(): string
    {
        return $this->rootPath . $this->dirSeparator . $this->file;
    }

    /**
     * @throws FileException
     */
    public function create(?string $in = null, ?string $name = null, ?string $extension = null): static
    {
        // prevent filename from existing
        $in = $in ? $this->dirPath($in) : $this->defaultPath();
        while ($this->disk->fileExists($file = $in . $this->dirSeparator . compose_filename(null, $extension))) {
        }

        $realpath = $this->rootPath . $this->dirSeparator . $file;
        mkdir_for_writing(dirname($realpath));
        if (($f = fopen($realpath, Filer::FILE_MODE_WRITE_FRESHLY)) === false) {
            throw new RuntimeException(sprintf('Cannot create a file at [%s]', $realpath));
        }
        fclose($f);
        return parent::setFile($file)
            ->setName($name ? compose_filename($name, $extension) : basename($file))
            ->setMimeType(guess_mime_type($extension))
            ->setExtension($extension)
            ->setSize(0);
    }
}
