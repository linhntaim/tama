<?php

namespace App\Support\Models\Contracts;

interface HasApiTokens
{
    public function retrieveToken(): mixed;
}
