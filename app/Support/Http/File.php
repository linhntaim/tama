<?php

namespace App\Support\Http;

use App\Support\Exceptions\FileException;
use App\Support\Exceptions\FileNotFoundException;
use Illuminate\Http\File as BaseFile;
use Illuminate\Http\UploadedFile;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException as SymfonyFileNotFoundException;

class File extends BaseFile
{
    /**
     * @throws FileNotFoundException
     */
    public static function from(string|SplFileInfo $file, bool $checkPath = true): static|UploadedFile
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        if ($file instanceof SplFileInfo) {
            return new static($file->getRealPath(), $checkPath);
        }
        try {
            return new static($file, $checkPath);
        }
        catch (SymfonyFileNotFoundException) {
            throw new FileNotFoundException($file);
        }
    }

    /**
     * @throws FileException
     */
    protected function getTargetFile(string $directory, string $name = null): static
    {
        mkdir_for_writing($directory);

        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $this->getName($name));

        return new static($target, false);
    }

    /**
     * @throws FileException
     */
    public function copy(string $directory, string $name = null): static
    {
        $target = $this->getTargetFile($directory, $name);

        set_error_handler(static function ($type, $msg) use (&$error) {
            $error = $msg;
        });
        try {
            $renamed = copy($this->getPathname(), $target);
        }
        finally {
            restore_error_handler();
        }
        if (!$renamed) {
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s).', $this->getPathname(), $target, strip_tags($error)));
        }

        @chmod($target, 0666 & ~umask());

        return $target;
    }
}
