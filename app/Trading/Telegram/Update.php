<?php

namespace App\Trading\Telegram;

use App\Support\ArrayReader;

class Update extends ArrayReader
{
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
