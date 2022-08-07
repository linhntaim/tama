<?php

namespace App\Trading\Bots;

class BotFactory
{
    public static function create(string $botName = OscillatingBot::NAME, array $botOptions = []): Bot
    {
        $botClass = match ($botName) {
            default => OscillatingBot::class
        };
        return new $botClass($botOptions);
    }
}
