<?php

namespace App\Trading\Bots;

abstract class BotReporter
{
    public function __construct(
        protected Bot $bot
    )
    {
    }

    public function report(): string
    {
        return $this->print($this->bot->indicate());
    }

    protected abstract function print(array $indication): string;
}
