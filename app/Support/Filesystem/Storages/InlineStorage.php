<?php

namespace App\Support\Filesystem\Storages;

class InlineStorage extends Storage
{
    public const NAME = 'inline';

    protected ?string $base64Data = null;

    public function setData(string $base64Data): static
    {
        if (is_base64($base64Data)) {
            $this->base64Data = $base64Data;
        }
        return $this;
    }

    public function has(): bool
    {
        return !is_null($this->base64Data);
    }
}