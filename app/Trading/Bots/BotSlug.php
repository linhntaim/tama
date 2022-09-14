<?php

namespace App\Trading\Bots;

trait BotSlug
{
    protected function slugConcat(string ...$parts): string
    {
        return implode('-', $parts);
    }
}
