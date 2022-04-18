<?php

namespace App\Support\Filesystem\Storages;

use App\Support\Http\File;
use BadMethodCallException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExternalStorage extends Storage implements IHasExternalStorage
{
    public const NAME = 'external';

    protected ?string $url = null;

    public function getExtension(): string
    {
        if (($extension = parent::getExtension()) == '') {
            if ($this->mimeType == 'text/html') {
                return 'html';
            }
        }
        return $extension;
    }

    public function getContent(): string
    {
        return file_get_contents($this->url);
    }

    public function getContentAsStream()
    {
        return ($f = fopen($this->url, 'r')) !== false
            ? $f : null;
    }

    public function has(): bool
    {
        return !is_null($this->url);
    }

    public function from(string|UploadedFile|File|Storage $file): static
    {
        throw new BadMethodCallException('External Storage does not support `from` method.');
    }

    public function setUrl(string $url): static
    {
        try {
            if (($response = Http::withoutRedirecting()->get($url))->ok()) {
                $this->url = $url;
                $this->setName(basename($url))
                    ->setMimeType(trim(explode(';', $response->header('content-type'))[0]))
                    ->setSize((int)$response->header('content-length'));
            }
        }
        catch (Throwable $exception) {
            report($exception);
        }
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}