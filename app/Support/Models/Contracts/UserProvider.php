<?php

namespace App\Support\Models\Contracts;

use App\Support\Models\User;

interface UserProvider
{
    public function firstByUsername(string $username, $value): ?User;
}
