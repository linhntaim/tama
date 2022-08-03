<?php

namespace App\Trading\Telegram;

class MarkdownText extends MarkdownString
{
    public function __construct(string|MarkdownString $string = '')
    {
        parent::__construct($this->escape($string));
    }

    public function append(string|MarkdownString $string): static
    {
        $this->string .= $this->escape($string);
        return $this;
    }

    protected function escape(string|MarkdownString $string): string
    {
        return $string instanceof MarkdownString
            ? $string :
            str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $string);
    }
}
