<?php

namespace App\Support\Notifications\Contracts;

interface Notifier
{
    public function getNotifierKey();

    public function getNotifierDisplayName();
}
