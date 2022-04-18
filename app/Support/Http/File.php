<?php

namespace App\Support\Http;

use Illuminate\Http\File as BaseFile;
use Illuminate\Http\UploadedFile;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class File extends BaseFile
{
    public static function from(string|SplFileInfo $file): static|UploadedFile
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        elseif ($file instanceof SplFileInfo) {
            return new static($file->getRealPath());
        }
        return new static($file);
    }

    protected function getTargetFile(string $directory, string $name = null): static
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true) && !is_dir($directory)) {
                throw new FileException(sprintf('Unable to create the "%s" directory.', $directory));
            }
        }
        elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory.', $directory));
        }

        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $this->getName($name));

        return new static($target, false);
    }

    public function copy(string $directory, string $name = null): static
    {
        $target = $this->getTargetFile($directory, $name);

        set_error_handler(function ($type, $msg) use (&$error) {
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