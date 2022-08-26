<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Models\UserSocialProvider;
use App\Support\Client\DateTimer;
use App\Trading\Bots\BotFactory;
use App\Trading\Bots\Exchanges\Factory as ExchangeFactory;
use App\Trading\Bots\Oscillators\RsiOscillator;
use App\Trading\Bots\Pricing\PriceProviderFactory;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use App\Trading\Trader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use JsonException;

class SubscribeCommand extends Command
{
    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Subscribe a trading.';

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

    /**
     * @throws JsonException
     */
    protected function botOptions(): array
    {
        return json_decode_array($this->option('bot-options') ?? '') ?: [
            'oscillator' => [
                'name' => RsiOscillator::NAME,
            ],
        ];
    }

    /**
     * @throws JsonException
     */
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

    /**
     * @throws JsonException
     */
    protected function handling(): int
    {
        if (!ExchangeFactory::enabled($this->exchange())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Subscription for the exchange "%s" was not supported/enabled.', $this->exchange())
            );
        }
        elseif (in_array($this->interval(), [
            Trader::INTERVAL_1_MINUTE,
            Trader::INTERVAL_3_MINUTES,
            Trader::INTERVAL_5_MINUTES,
        ], true)) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                sprintf('Subscription for the interval "%s" was not supported/enabled.', $this->interval())
            );
        }
        elseif (is_null($user = $this->createUserFromTelegram())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Subscription was not supported.'
            );
        }
        else {
            $redis = Redis::connection(trading_cfg_redis_pubsub_connection());
            if ($this->ticker()[0] === '*') {
                $tickers = $this->fetchTickers();
                foreach ($tickers as $ticker) {
                    $this->subscribe($user, $this->createTrading([
                        'ticker' => $ticker,
                        'safe_ticker' => true,
                    ]), $redis);
                }
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscriptions to %s trading(s) were created successfully.', $tickers->count())
                );
            }
            else {
                $this->subscribe($user, $trading = $this->createTrading(), $redis);
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscription to the trading {#%s:%s} was created successfully.', $trading->id, $trading->slug)
                );
            }
        }
        return $this->exitSuccess();
    }

    protected function fetchTickers(): Collection
    {
        return PriceProviderFactory::create($this->exchange())->availableTickers($this->ticker());
    }

    protected function createUserFromTelegram(): ?User
    {
        if (is_null($chat = $this->telegramUpdate->getChat())) {
            return null;
        }
        return match ($chat['type']) {
            'private' => $this->createUser(
                $chat['firstname'] . ' ' . $chat['lastname'],
                $chat['username'] . '@telegram.private',
                $chat['id'],
            ),
            'group' => $this->createUser(
                $chat['title'],
                $chat['id'] . '@telegram.group',
                $chat['id'],
            ),
            'supergroup' => $this->createUser(
                $chat['title'],
                $chat['id'] . '@telegram.supergroup',
                $chat['id'],
            ),
            'channel' => $this->createUser(
                $chat['title'],
                $chat['id'] . '@telegram.channel',
                $chat['id'],
            ),
            default => null,
        };
    }

    protected function createUser(string $name, string $email, string $providerId): User
    {
        return modify(
            ($userProvider = new UserProvider())
                ->notStrict()
                ->firstByEmail($email),
            static function ($user) use ($userProvider, $name, $email, $providerId) {
                return take(
                    is_null($user) ? $userProvider->createWithAttributes([
                        'email' => $email,
                        'name' => $name,
                        'password' => Str::random(40),
                        'email_verified_at' => DateTimer::databaseNow(),
                    ]) : $user,
                    static function (User $user) use ($providerId) {
                        (new UserSocialProvider())->firstOrCreateWithAttributes([
                            'user_id' => $user->id,
                            'provider' => 'telegram',
                        ], [
                            'provider_id' => $providerId,
                        ]);
                    }
                );
            });
    }

    /**
     * @throws JsonException
     */
    protected function createTrading(array $botOptions = []): Trading
    {
        return modify(
            ($tradingProvider = new TradingProvider())
                ->notStrict()
                ->firstBySlug($slug = ($bot = BotFactory::create($this->bot(), $this->mergeBotOptions($botOptions)))->asSlug()),
            static function ($trading) use ($tradingProvider, $bot, $slug) {
                return is_null($trading)
                    ? $tradingProvider->createWithAttributes([
                        'slug' => $slug,
                        'bot' => $bot->getName(),
                        'exchange' => $bot->exchange(),
                        'ticker' => $bot->ticker(),
                        'interval' => (string)$bot->interval(),
                        'options' => $bot->options(),
                    ])
                    : $trading;
            }
        );
    }

    /**
     * @throws JsonException
     */
    protected function subscribe(User $user, Trading $trading, $redis): void
    {
        $trading->subscribers()->syncWithoutDetaching([
            $user->id => [
                'subscribed_at' => DateTimer::databaseNow(),
            ],
        ]);
        $redis->publish('price-stream:subscribe', json_encode_readable([
            'exchange' => $trading->exchange,
            'ticker' => $trading->ticker,
            'interval' => $trading->interval,
        ]));
    }
}
