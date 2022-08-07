<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\BotReporter;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class TradesCommand extends Command
{
    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--latest=1} {--bot-options=}';

    protected $description = 'Get latest possible tradings.';

    protected function bot(): string
    {
        return $this->option('bot') ?? 'oscillating_bot';
    }

    protected function exchange(): string
    {
        return $this->option('exchange') ?? 'binance';
    }

    protected function ticker(): string
    {
        return $this->option('ticker') ?? 'BTCUSDT';
    }

    protected function interval(): string
    {
        return $this->option('interval') ?? '1d';
    }

    protected function latest(): int
    {
        return modify((int)($this->option('latest') ?? 1), function (int $latest) {
            return $latest > 0 && $latest <= 5 ? $latest : 1;
        });
    }

    protected function botOptions(): array
    {
        return not_null_or(json_decode_array($this->option('bot-options') ?? ''), []);
    }

    protected function mergeBotOptions(): array
    {
        return array_merge([
            'exchange' => $this->exchange(),
            'ticker' => $this->ticker(),
            'interval' => $this->interval(),
        ], $this->botOptions());
    }

    protected function handling(): int
    {
        ConsoleNotification::send(
            new TelegramUpdateNotifiable($this->telegramUpdate),
            $this->report()
        );
        return $this->exitSuccess();
    }

    protected function report(): string
    {
        return $this->reportIndications(BotFactory::create($this->bot(), $this->mergeBotOptions()));
    }

    protected function reportIndications(Bot $bot): string
    {
        return (new BotReporter())->report($bot, $bot->indicate(null, $this->latest()));
    }
}
