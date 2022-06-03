<?php

namespace App\Support\Notifications;

use App\Support\Models\ModelProvider;
use Illuminate\Support\Str;

class DatabaseNotificationProvider extends ModelProvider
{
    public string $modelClass = DatabaseNotification::class;

    public function generateUniqueId(): string
    {
        return $this->generateUniqueValue('id', fn() => $this->makeUniqueId());
    }

    protected function makeUniqueId(): string
    {
        return Str::uuid()->toString();
    }
}
