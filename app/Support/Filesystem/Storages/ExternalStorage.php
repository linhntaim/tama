<?php

namespace App\Support\Filesystem\Storages;

use BadMethodCallException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SplFileInfo;
use Throwable;

class ExternalStorage extends Storage implements IHasExternalStorage
{
    use HasUrlStorage;

    public const NAME = 'external';

    protected string $visibility = Filesystem::VISIBILITY_PUBLIC;

    public function fromFile(Storage|SplFileInfo|string $file): static
    {
        throw new BadMethodCallException('External Storage does not support `from` method.');
    }

    public function setFile(SplFileInfo|string $file): static
    {
        if (is_url($file)) {
            try {
                if (($response = Http::withoutRedirecting()->head($file))->ok()) {
                    return parent::setFile($file)
                        ->setName(basename($file))
                        ->setMimeType(trim(explode(';', $response->header('content-type'))[0]))
                        ->setExtension(
                            ($extension = pathinfo(parse_url($file)['path'] ?? '', PATHINFO_EXTENSION)) == ''
                                ? guess_extension($this->mimeType) : $extension
                        )
                        ->setSize((int)$response->header('content-length'));
                }
            }
            catch (Throwable $exception) {
                report($exception);
            }
        }
        return $this;
    }

    public function getContent(): string
    {
        return file_get_contents($this->file);
    }

    public function getStream()
    {
        if (($f = fopen($this->file, 'r')) === false) {
            throw new RuntimeException(sprintf('Cannot get stream from the url [%s]', $this->file));
        }
        return $f;
    }

    public function getUrl(): string
    {
        return $this->file;
    }
}
