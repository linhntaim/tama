<?php

namespace App\Trading\Telegram;

use App\Support\ArrayReader;

class Update extends ArrayReader
{
    public function isPrivate(): bool
    {
        return $this->get('message.chat.type') === 'private';
    }

    public function isChannel(): bool
    {
        return isset($this->data['channel_post']) || isset($this->data['edited_channel_post']);
    }

    public function fromUsername(): string
    {
        return $this->get(
            'message.from.username',
            $this->get(
                'channel_post.sender_chat.username',
                $this->get(
                    'edited_message.from.username',
                    $this->get('edited_channel_post.sender_chat.username')
                )
            )
        );
    }

    public function getChat(): array
    {
        return $this->get(
            'message.chat',
            $this->get(
                'channel_post.chat',
                $this->get(
                    'edited_message.chat',
                    $this->get('edited_channel_post.chat')
                )
            )
        );
    }

    public function chatId(bool $private = false): int
    {
        return !$private
            ? $this->get(
                'message.chat.id',
                $this->get(
                    'channel_post.chat.id',
                    $this->get(
                        'edited_message.chat.id',
                        $this->get('edited_channel_post.chat.id')
                    )
                )
            )
            : $this->get(
                'message.from.id',
                $this->get(
                    'channel_post.sender_chat.id',
                    $this->get(
                        'edited_message.from.id',
                        $this->get('edited_channel_post.sender_chat.id')
                    )
                )
            );
    }
}
