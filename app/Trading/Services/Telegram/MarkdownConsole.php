<?php

namespace App\Trading\Services\Telegram;

class MarkdownConsole extends MarkdownString
{
    public function __toString(): string
    {
        return '```' . PHP_EOL . $this->string . PHP_EOL . '```';
    }
}
