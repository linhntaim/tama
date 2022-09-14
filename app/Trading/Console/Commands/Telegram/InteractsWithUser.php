<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Models\UserSocialProvider;
use App\Support\Client\DateTimer;
use App\Trading\Notifications\Telegram\ConsoleNotification;
use App\Trading\Notifications\TelegramUpdateNotifiable;
use App\Trading\Services\Telegram\Update as TelegramUpdate;
use Illuminate\Support\Str;
use InvalidArgumentException;

trait InteractsWithUser
{
    protected TelegramUpdate $telegramUpdate;

    protected function getTelegramUpdate(): TelegramUpdate
    {
        return $this->telegramUpdate
            ?? $this->telegramUpdate = new TelegramUpdate(
                json_decode_array(
                    base64_decode(
                        take(
                            $this->option('telegram-update'),
                            static function ($telegram) {
                                if (is_null($telegram)) {
                                    throw new InvalidArgumentException('Telegram update option must be provided.');
                                }
                            }
                        )
                    )
                )
            );
    }

    protected function getTelegramNotifiable(): TelegramUpdateNotifiable
    {
        return new TelegramUpdateNotifiable($this->getTelegramUpdate());
    }

    protected function sendConsoleNotification(string $text): void
    {
        ConsoleNotification::send($this->getTelegramNotifiable(), $text);
    }

    protected function validateCreatingUser(): User|bool
    {
        if (is_null($user = $this->createUserFromTelegram())) {
            $this->sendConsoleNotification('Action is  not supported.');
            return false;
        }
        return $user;
    }

    protected function validateFindingUser(): User|bool
    {
        if (is_null($user = $this->findUser())) {
            $this->sendConsoleNotification('Action is  not supported.');
            return false;
        }
        return $user;
    }

    protected function createUserFromTelegram(): ?User
    {
        if (is_null($chat = $this->getTelegramUpdate()->getChat())) {
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
        return with(
            ($userProvider = new UserProvider())
                ->notStrict()
                ->firstByEmail($email),
            static function (?User $user) use ($userProvider, $name, $email, $providerId) {
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

    protected function findUser(): ?User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByProvider('telegram', $this->getTelegramUpdate()->chatId());
    }
}
