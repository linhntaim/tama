<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;
use App\Models\UserSocialProvider;
use App\Support\Client\DateTimer;
use Illuminate\Support\Str;

trait CreateUser
{
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
        return with(
            ($userProvider = new UserProvider())
                ->notStrict()
                ->firstByEmail($email),
            static function (?User $user) use ($userProvider, $name, $email, $providerId) {
                return tap(
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
}
