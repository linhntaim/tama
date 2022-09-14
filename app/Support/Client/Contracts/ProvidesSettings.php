<?php

namespace App\Support\Client\Contracts;

interface ProvidesSettings
{
    public function getLocale(): string;
}
