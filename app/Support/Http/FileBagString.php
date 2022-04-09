<?php

/**
 * Base
 */

namespace App\Support\Http;

use Illuminate\Http\UploadedFile;

class FileBagString extends BagString
{
    protected function stringifyItem(string $name, mixed $item): string|array
    {
        return parent::stringifyItem($name, $this->mapFile($item));
    }

    protected function mapFile(mixed $file): mixed
    {
        if (is_array($file)) {
            return array_map(fn($f) => $this->mapFile($f), $file);
        }
        if ($file instanceof UploadedFile) {
            return [
                'client' => [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                ],
                'uploaded' => [
                    'pathname' => $file->getPathname(),
                    'mime' => $file->getMimeType(),
                    'extension' => $file->extension(),
                    'size' => $file->getSize(),
                ],
            ];
        }
        return $file;
    }
}