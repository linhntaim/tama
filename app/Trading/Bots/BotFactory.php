<?php

namespace App\Trading\Bots;

class BotFactory
{
    public static function factory(string $botName, array $botOptions): Bot
    {
        $botClass = match ($botName) {
            default => OscillatingBot::class
        };
        return new $botClass($botOptions);
    }
}
