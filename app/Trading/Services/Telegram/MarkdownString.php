<?php

namespace App\Trading\Services\Telegram;

class MarkdownString
{
    public function __construct(protected string $string)
    {
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
