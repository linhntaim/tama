<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\BotReporter;
use App\Trading\Bots\Exchanges\Factory as ExchangeFactory;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

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
        return modify((int)($this->option('latest') ?? 1), static function (int $latest) {
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

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function handling(): int
    {
        if (!ExchangeFactory::enabled($this->exchange())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Exchange was not supported/enabled.'
            );
        }
        else {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                $this->report()
            );
        }
        return $this->exitSuccess();
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function report(): string
    {
        return $this->reportIndications(BotFactory::create($this->bot(), $this->mergeBotOptions()));
    }

    /**
     * @throws PsrInvalidArgumentException
     */
    protected function reportIndications(Bot $bot): string
    {
        return (new BotReporter())->report($bot, $bot->indicate($this->latest()));
    }
}
