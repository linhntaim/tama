<?php

namespace App\Support\TradingSystem\Bots;

abstract class Bot
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    public abstract function act(): void;
}
