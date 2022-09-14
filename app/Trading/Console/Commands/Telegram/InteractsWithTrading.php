<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Support\Client\DateTimer;
use App\Trading\Bots\Bot;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Exchanger;
use App\Trading\Bots\Exchanges\Ticker;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Trader;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

trait InteractsWithTrading
{
    use InteractsWithPriceStream;

    protected string $bot;

    protected string $exchange;

    protected string $ticker;

    protected string $interval;

    protected function bot(): ?string
    {
        return is_null($option = $this->option('bot'))
            ? null
            : ($this->bot ?? $this->bot = strtolower($option));
    }

    protected function exchange(): ?string
    {
        return is_null($option = $this->option('exchange'))
            ? null
            : ($this->exchange ?? $this->exchange = strtolower($option));
    }

    protected function ticker(): ?string
    {
        return is_null($option = $this->option('ticker'))
            ? null
            : ($this->ticker ?? $this->ticker = strtoupper($option));
    }

    protected function baseSymbol(): bool|string
    {
        if (!is_null($ticker = $this->ticker()) && $ticker[strlen($ticker) - 1] === '*') {
            return substr($ticker, 0, -1);
        }
        return false;
    }

    protected function quoteSymbol(): bool|string
    {
        if (!is_null($ticker = $this->ticker()) && $ticker[0] === '*') {
            return substr($ticker, 1);
        }
        return false;
    }

    /**
     * @return bool|Collection<int, Ticker>
     */
    protected function tickers(): bool|Collection
    {
        if (($baseSymbol = $this->baseSymbol()) !== false) {
            return Exchanger::connector($this->exchange())
                ->availableTickers(null, $baseSymbol);
        }
        if (($quoteSymbol = $this->quoteSymbol()) !== false) {
            return Exchanger::connector($this->exchange())
                ->availableTickers(
                    $quoteSymbol,
                    null,
                    null,
                    array_merge(Exchanger::STABLECOIN_SYMBOLS, Exchanger::GOLDCOIN_SYMBOLS)
                );
        }
        return false;
    }

    protected function interval(): ?string
    {
        return is_null($option = $this->option('interval'))
            ? null
            : ($this->interval ?? $this->interval = $option);
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

    protected function createBot(array $botOptions = []): Bot
    {
        return BotFactory::create($this->bot(), $this->mergeBotOptions($botOptions));
    }

    protected function validateExchange(): bool
    {
        if (!Exchanger::available($this->exchange())) {
            $this->sendConsoleNotification(sprintf('Exchange "%s" was not supported/enabled.', $this->exchange()));
            return false;
        }
        return true;
    }

    protected function validateInterval(): bool
    {
        if (in_array($this->interval(), [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
        ], true)) {
            $this->sendConsoleNotification(sprintf('Interval "%s" was not supported.', $this->interval()));
            return false;
        }
        return true;
    }

    protected function validateInputs(): bool
    {
        return $this->validateExchange()
            && $this->validateInterval();
    }

    protected function createTrading(array $botOptions = []): Trading
    {
        return with(
            ($tradingProvider = new TradingProvider())
                ->notStrict()
                ->firstBySlug($slug = ($bot = $this->createBot($botOptions))->asSlug()),
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

    protected function findTrading(?int $id, ?User $user = null): ?Trading
    {
        return is_null($id) ? null : (new TradingProvider())
            ->notStrict()
            ->first(array_filter([
                'id' => $id,
                'subscriber' => $user,
            ]));
    }

    /**
     * @param User|null $user
     * @return EloquentCollection<int, Trading>|null
     */
    protected function findTradings(?User $user = null): ?EloquentCollection
    {
        $conditions = array_filter(
            array_merge(
                [
                    'bot' => $this->bot(),
                    'exchange' => $this->exchange(),
                    'interval' => $this->interval(),
                ],
                match (true) {
                    ($baseSymbol = $this->baseSymbol()) !== false => [
                        'base_symbol' => $baseSymbol,
                    ],
                    ($quoteSymbol = $this->quoteSymbol()) !== false => [
                        'quote_symbol' => $quoteSymbol,
                    ],
                    default => [
                        'ticker' => $this->ticker(),
                    ]
                }
            )
        );
        return count($conditions)
            ? (new TradingProvider())->all($conditions + array_filter(['subscriber' => $user]))
            : null;
    }

    protected function subscribe(User $user, Trading $trading): void
    {
        $trading->subscribers()->syncWithoutDetaching([
            $user->id => [
                'subscribed_at' => DateTimer::databaseNow(),
            ],
        ]);
        $this->subscribePriceStream($trading);
    }

    protected function unsubscribe(User $user, Trading $trading): void
    {
        $trading->subscribers()->detach($user->id);
        $this->unsubscribePriceStream($trading);
    }
}
