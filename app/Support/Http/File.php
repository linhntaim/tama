<?php

namespace App\Support\Http;

use Illuminate\Http\File as BaseFile;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use SplFileObject;

class File extends BaseFile
{
    public static function from(object|string $file): UploadedFile|static
    {
        if ($file instanceof UploadedFile) {
            return $file;
        }
        elseif ($file instanceof SplFileObject) {
            return new static($file->getRealPath());
        }
        elseif (is_string($file)) {
            return new static($file);
        }
        throw new InvalidArgumentException(sprintf('File [%s] not supported.', get_debug_type($file)));
    }
}