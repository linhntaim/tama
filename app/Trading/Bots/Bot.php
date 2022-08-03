<?php

namespace App\Trading\Bots;

abstract class Bot
{
    public function __construct(
        protected array $options = []
    )
    {
    }

    public abstract function indicate(): array;

    public abstract function determine(): array;

    public abstract function broadcast(): array;
}
