<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;

class TradesCommand extends Command
{
    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--latest=1} {--bot-options=}';

    protected $description = 'Get latest possible tradings.';

    protected string $bot;

    protected string $exchange;

    protected string $ticker;

    protected string $interval;

    protected function bot(): string
    {
        return $this->bot ?? $this->bot = strtolower($this->option('bot'));
    }

    protected function exchange(): string
    {
        return $this->exchange ?? $this->exchange = strtolower($this->option('exchange'));
    }

    protected function ticker(): string
    {
        return $this->ticker ?? $this->ticker = strtoupper($this->option('ticker'));
    }

    protected function interval(): string
    {
        return $this->interval ?? $this->interval = $this->option('interval');
    }

    protected function latest(): int
    {
        return with((int)($this->option('latest') ?? 1), static function (int $latest) {
            return $latest > 0 && $latest <= 5 ? $latest : 1;
        });
    }

    protected function botOptions(): array
    {
        return json_decode_array($this->option('bot-options')) ?: [
            'oscillator' => [
                'name' => RsiOscillator::NAME,
            ],
        ];
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
        if (!Exchanger::available($this->exchange())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Exchange was not supported/enabled.'
            );
        }
        else {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                $this->report() ?: 'No trade found.'
            );
        }
        return $this->exitSuccess();
    }

    protected function report(): ?string
    {
        return BotFactory::create($this->bot(), $this->mergeBotOptions())->report($this->latest());
    }
}
