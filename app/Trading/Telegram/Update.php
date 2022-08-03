<?php

namespace App\Trading\Telegram;

class Update
{
    public function __construct(
        protected array $update
    )
    {
    }

    public function get(string $key, $default = null): mixed
    {
        return data_get($this->update, $key, $default);
    }

    public function isPrivate(): bool
    {
        return $this->get('message.chat.type') == 'private';
    }

    public function chatId(bool $private = false)
    {
        return !$private
            ? $this->get('message.chat.id')
            : $this->get('message.from.id');
    }
}
