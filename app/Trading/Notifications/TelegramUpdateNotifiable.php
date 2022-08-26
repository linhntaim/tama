<?php

namespace App\Trading\Notifications;

use App\Support\Notifications\AnonymousNotifiable;
use App\Trading\Telegram\Update as TelegramUpdate;
use BadMethodCallException;

/**
 * @method mixed get(string $key, mixed $default = null)
 * @method string fromUsername()
 * @method bool isChannel()
 */
class TelegramUpdateNotifiable extends AnonymousNotifiable
{
    public function __construct(
        protected TelegramUpdate $telegramUpdate,
        protected bool           $private = false
    )
    {
    }

    public function getKey()
    {
        return $this->telegramUpdate->get('update_id');
    }

    public function routeNotificationFor($driver, $notification = null)
    {
        if ($driver === 'telegram') {
            return $this->telegramUpdate->chatId($this->private);
        }
        return parent::routeNotificationFor($driver, $notification);
    }

    public function __call(string $name, array $arguments)
    {
        if (method_exists($this->telegramUpdate, $name)) {
            return $this->telegramUpdate->{$name}(...$arguments);
        }
        throw new BadMethodCallException('Method does not exist.');
    }
}
