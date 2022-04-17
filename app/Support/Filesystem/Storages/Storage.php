<?php

namespace App\Support\Filesystem\Storages;

abstract class Storage
{
    public const NAME = 'storage';

    protected string $name;

    protected string $mimeType;

    protected int $size;

    public abstract function has(): bool;
}