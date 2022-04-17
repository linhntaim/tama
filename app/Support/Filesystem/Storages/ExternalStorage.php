<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Support\Facades\Http;
use Throwable;

class ExternalStorage extends Storage implements IHasExternalStorage
{
    public const NAME = 'external';

    protected ?string $url = null;

    public function setUrl(string $url): static
    {
        try {
            if (Http::withoutRedirecting()->get($this->url)->ok()) {
                $this->url = $url;
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

    public function has(): bool
    {
        return !is_null($this->url);
    }
}