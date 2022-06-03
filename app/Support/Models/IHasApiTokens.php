<?php

namespace App\Support\Models;

interface IHasApiTokens
{
    public function retrieveToken(): mixed;
}
