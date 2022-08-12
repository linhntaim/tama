<?php

namespace App\Trading\Bots;

use InvalidArgumentException;

class BotFactory
{
    public static function create(string $botName = OscillatingBot::NAME, array $botOptions = []): Bot
    {
        $botClass = match ($botName) {
            OscillatingBot::NAME => OscillatingBot::class,
            default => throw new InvalidArgumentException(sprintf('Bot "%s" does not exist.', $botName))
        };
        return new $botClass($botOptions);
    }
}
