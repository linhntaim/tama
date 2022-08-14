<?php

namespace App\Trading\Bots;

use InvalidArgumentException;

class BotFactory
{
    public static function create(string $name, array $options): Bot
    {
        return match ($name) {
            OscillatingBot::NAME => new OscillatingBot($options),
            default => throw new InvalidArgumentException(sprintf('Bot "%s" does not exist.', $name))
        };
    }
}
