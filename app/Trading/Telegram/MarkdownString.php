<?php

namespace App\Trading\Telegram;

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
