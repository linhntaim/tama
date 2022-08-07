<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Models\UserSocialProvider;
use App\Support\Client\DateTimer;
use App\Trading\Bots\BotFactory;
use App\Trading\Exchanges\Connection;
use App\Trading\Models\Trading;
use App\Trading\Models\TradingProvider;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SubscribeCommand extends Command
{
    public $signature = '{--bot=oscillating_bot} {--exchange=binance} {--ticker=BTCUSDT} {--interval=1d} {--bot-options=}';

    protected $description = 'Subscribe a trading.';

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

    protected function botOptions(): array
    {
        return not_null_or(json_decode_array($this->option('bot-options') ?? ''), []);
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

    protected function handling(): int
    {
        if (is_null($user = $this->createUserFromTelegram())) {
            ConsoleNotification::send(
                new TelegramUpdateNotifiable($this->telegramUpdate),
                'Subscription was not supported.'
            );
        }
        else {
            if ($this->ticker()[0] == '*') {
                $tickers = $this->fetchTickers();
                foreach ($tickers as $ticker) {
                    $this->subscribe($user, $this->createTrading([
                        'ticker' => $ticker,
                        'safe_ticker' => true,
                    ]));
                }
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscriptions to %s trading(s) were created successfully.', $tickers->count())
                );
            }
            else {
                $this->subscribe($user, $trading = $this->createTrading());
                ConsoleNotification::send(
                    new TelegramUpdateNotifiable($this->telegramUpdate),
                    sprintf('Subscription to the trading {%s:%s} was created successfully.', $trading->id, $trading->slug)
                );
            }
        }
        return $this->exitSuccess();
    }

    protected function fetchTickers(): Collection
    {
        return Connection::create($this->exchange())->availableTickers($this->ticker());
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
            function ($user) use ($userProvider, $name, $email, $providerId) {
                return take(
                    is_null($user) ? $userProvider->createWithAttributes([
                        'email' => $email,
                        'name' => $name,
                        'password' => Str::random(40),
                        'email_verified_at' => DateTimer::databaseNow(),
                    ]) : $user,
                    function (User $user) use ($providerId) {
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

    protected function createTrading(array $botOptions = []): Trading
    {
        return modify(
            ($tradingProvider = new TradingProvider())
                ->notStrict()
                ->firstBySlug($slug = ($bot = BotFactory::create($this->bot(), $this->mergeBotOptions($botOptions)))->asSlug()),
            function ($trading) use ($tradingProvider, $bot, $slug) {
                return is_null($trading)
                    ? $tradingProvider->createWithAttributes([
                        'slug' => $slug,
                        'bot' => $bot->getName(),
                        'exchange' => $bot->exchange(),
                        'ticker' => $bot->ticker(),
                        'interval' => $bot->interval(),
                        'options' => $bot->options(),
                    ])
                    : $trading;
            }
        );
    }

    protected function subscribe(User $user, Trading $trading)
    {
        $trading->subscribers()->syncWithoutDetaching([
            $user->id => [
                'subscribed_at' => DateTimer::databaseNow(),
            ],
        ]);
    }
}
