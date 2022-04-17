<?php

namespace App\Support\Filesystem\Storages;

use Illuminate\Contracts\Filesystem\Filesystem;

class PublicStorage extends LocalStorage implements IHasPublicStorage
{
    use HasPublicStorage;

    public const NAME = 'public';

    public function __construct()
    {
        parent::__construct('public');

        $this->visibility = Filesystem::VISIBILITY_PUBLIC;
    }
}