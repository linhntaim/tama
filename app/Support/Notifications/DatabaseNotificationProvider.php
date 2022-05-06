<?php

namespace App\Support\Notifications;

use App\Support\Exceptions\DatabaseException;
use App\Support\Exceptions\Exception;
use App\Support\Models\ModelProvider;
use Illuminate\Support\Str;

class DatabaseNotificationProvider extends ModelProvider
{
    public function modelClass(): string
    {
        return DatabaseNotification::class;
    }

    /**
     * @throws DatabaseException|Exception
     */
    public function generateUniqueId(): string
    {
        return $this->generateUniqueValue('id', fn() => $this->makeUniqueId());
    }

    protected function makeUniqueId(): string
    {
        return Str::uuid()->toString();
    }
}