<?php

namespace App\Support\Models;

interface IUserProvider
{
    public function firstByUsername(string $username, $value): ?User;
}
