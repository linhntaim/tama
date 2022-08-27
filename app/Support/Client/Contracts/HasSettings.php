<?php

namespace App\Support\Client\Contracts;

use App\Support\Client\Settings;

interface HasSettings
{
    public function getSettings(): Settings|array;
}
