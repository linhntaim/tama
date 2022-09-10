<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;

trait CreateTrading
{
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

    protected function botOptions(): array
    {
        return json_decode_array($this->option('bot-options')) ?: [
            'oscillator' => [
                'name' => RsiOscillator::NAME,
            ],
        ];
    }

    protected function mergeBotOptions(array $botOptions = []): array
    {
        return array_merge(
            [
                'exchange' => $this->exchange(),
                'ticker' => $this->ticker(),
                'interval' => $this->interval(),
            ],
            $this->botOptions(),
            $botOptions
        );
    }

    protected function createTrading(array $botOptions = []): Trading
    {
        return with(
            ($tradingProvider = new TradingProvider())
                ->notStrict()
                ->firstBySlug($slug = ($bot = BotFactory::create($this->bot(), $this->mergeBotOptions($botOptions)))->asSlug()),
            static function (?Trading $trading) use ($tradingProvider, $bot, $slug) {
                return is_null($trading)
                    ? $tradingProvider->createWithAttributes([
                        'slug' => $slug,
                        'bot' => $bot->getName(),
                        'exchange' => $bot->exchange(),
                        'ticker' => (string)$bot->ticker(),
                        'base_symbol' => $bot->baseSymbol(),
                        'quote_symbol' => $bot->quoteSymbol(),
                        'interval' => (string)$bot->interval(),
                        'options' => $bot->options(),
                    ])
                    : $trading;
            }
        );
    }
}
