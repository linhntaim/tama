<?php

namespace App\Support\Client;

interface IHasSettings
{
    public function getSettings(): Settings|array;
}