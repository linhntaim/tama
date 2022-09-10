<?php

namespace App\Trading\Console\Commands\Telegram;

use App\Models\User;
use App\Models\UserProvider;

trait FindUser
{
    protected function findUser(): ?User
    {
        return (new UserProvider())
            ->notStrict()
            ->firstByProvider('telegram', $this->telegramUpdate->chatId());
    }
}
